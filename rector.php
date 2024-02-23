<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use Rector\CodeQuality\Rector\NullsafeMethodCall\CleanupUnneededNullsafeOperatorRector;
use Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Php82\Rector\Param\AddSensitiveParameterAttributeRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector;
use Rector\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector;

use Rector\TypeDeclaration\Rector\ClassMethod\{
    AddMethodCallBasedStrictParamTypeRector,
    AddParamTypeDeclarationRector,
    AddParamTypeFromPropertyTypeRector,
    AddVoidReturnTypeWhereNoReturnRector,
    ParamTypeByMethodCallTypeRector,
    ParamTypeByParentCallTypeRector,
    ReturnNeverTypeRector,
    ReturnUnionTypeRector
};

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src/Security'
    ])
    ->withSkip([
        __DIR__ . '/src/Console/vendor'
    ])
    ->withPhpSets(php83: true)
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
        ArrayKeyExistsTernaryThenValueToCoalescingRector::class,
        CleanupUnneededNullsafeOperatorRector::class,
        RemoveUnusedVariableInCatchRector::class,
        MixedTypeRector::class,
        StringableForToStringRector::class,
        AddSensitiveParameterAttributeRector::class,
        AddTypeToConstRector::class,
        AddOverrideAttributeToOverriddenMethodsRector::class,
        AddMethodCallBasedStrictParamTypeRector::class,
        AddParamTypeDeclarationRector::class,
        AddParamTypeFromPropertyTypeRector::class,
        AddPropertyTypeDeclarationRector::class,
        EmptyOnNullableObjectToInstanceOfRector::class,
        ParamTypeByMethodCallTypeRector::class,
        ParamTypeByParentCallTypeRector::class,
        ReturnNeverTypeRector::class,
        ReturnUnionTypeRector::class
    ]);
