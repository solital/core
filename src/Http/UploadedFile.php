<?php

declare(strict_types=1);

namespace Solital\Core\Http;

use Psr\Http\Message\{StreamInterface, UploadedFileInterface};
use Solital\Core\Exceptions\{RuntimeException, InvalidArgumentException};

class UploadedFile implements UploadedFileInterface
{
    /**
     * The client-provided full path to the file.
     *
     * @var string
     */
    private string $file;

    /**
     * The stream instance.
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    private StreamInterface $stream;

    /**
     * The size of the file in bytes.
     *
     * @var int
     */
    private int $size;

    /**
     * The PHP UPLOAD_ERROR_* constant provided by the uploader.
     *
     * @var int
     */
    private int $error;

    /**
     * The client-provided file name.
     *
     * @var null|string
     */
    private ?string $clientFilename;

    /**
     * The client-provided media type of the file.
     *
     * @var null|string
     */
    private ?string $clientMediaType;

    /**
     * Indicates if the uploaded file has already been moved.
     *
     * @var bool
     */
    private bool $moved = false;

    /**
     * Indicates if the upload is from a SAPI environment.
     *
     * @var bool
     */
    private bool $sapi;

    /**
     * Create a new uploaded file instance.
     *
     * @param string|resource|\Psr\Http\Message\StreamInterface $file
     * @param int                                               $size
     * @param int                                               $error
     * @param string|null                                       $clientFilename
     * @param string|null                                       $clientMediaType
     * @param bool                                              $sapi
     *
     * @throws \InvalidArgumentException if the file isn't a string, resource or Stream instance.
     */
    public function __construct(
        $file,
        int $size = 0,
        int $error = UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null,
        bool $sapi = false
    ) {
        $this->setFileAndStream($file);
        $this->size = $size;

        if ($error < 0 || $error > 8) {
            throw new InvalidArgumentException("The error status must be an UPLOAD_ERR_* constant", 400);
        }

        $this->error = $error;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
        $this->sapi = $sapi;
    }

    /**
     * Set the file name and stream instance.
     *
     * @param string|resource|\Psr\Http\Message\StreamInterface $file
     *
     * @throws \InvalidArgumentException if the file isn't a string, resource or Stream instance.
     */
    private function setFileAndStream($file)
    {
        if (is_string($file)) {
            $this->file = $file;
            $this->stream = new Stream($file, 'wb+');
        } elseif (is_resource($file)) {
            $this->stream = new Stream($file);
            $this->file = $this->stream->getMetadata('uri');
        } elseif ($file instanceof StreamInterface) {
            $this->file = $file->getMetadata('uri');
            $this->stream = $file;
        } else {
            throw new InvalidArgumentException("The file must be a string, resource or instance of Psr\Http\Message\StreamInterface", 400);
        }
    }

    /**
     * @return \Psr\Http\Message\StreamInterface Stream representation of the uploaded file.
     *
     * @throws \RuntimeException in cases when no stream is available or can be created.
     */
    public function getStream(): StreamInterface
    {
        if ($this->moved) {
            throw new RuntimeException("Cannot retrieve stream as it was moved");
        }

        return $this->stream;
    }

    /**
     * @param string $targetPath Path to which to move the uploaded file.
     *
     * @throws \InvalidArgumentException if the $targetPath specified is invalid.
     * @throws \RuntimeException on any error during the move operation, or on the second or subsequent call to the
     *                           method.
     */
    #[\Override]
    public function moveTo($targetPath): void
    {
        if (empty($targetPath) || !is_string($targetPath)) {
            throw new InvalidArgumentException("The target path must be a non-empty string", 400);
        }

        $targetIsStream = strpos($targetPath, '://') > 0;

        if (!$targetIsStream && !is_writable(dirname($targetPath))) {
            throw new InvalidArgumentException("The upload target path ' . $targetPath . ' is not writable", 400);
        }

        if ($this->moved) {
            throw new RuntimeException("The uploaded file was already moved");
        }

        if ($targetIsStream) {
            if (!copy($this->file, $targetPath)) {
                throw new RuntimeException("The file " . $this->file . ' could not be moved to ' . $targetPath);
            }

            if (!unlink($this->file)) {
                throw new RuntimeException("The file " . $this->file . ' could not be removed');
            }
        } elseif ($this->sapi) {
            if (!move_uploaded_file($this->file, $targetPath)) {
                throw new RuntimeException("The file " . $this->file . '"could not be moved to ' . $targetPath);
            }
        } elseif (!rename($this->file, $targetPath)) {
            throw new RuntimeException("The file " . $this->file . ' could not be moved to ' . $targetPath);
        }

        $this->moved = true;
    }

    /**
     * @return int|null The file size in bytes or null if unknown.
     */
    #[\Override]
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * @return string|null The filename sent by the client or null if none was provided.
     */
    #[\Override]
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * @return string|null The media type sent by the client or null if none was provided.
     */
    #[\Override]
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }
}
