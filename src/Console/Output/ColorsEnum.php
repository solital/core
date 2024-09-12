<?php

namespace Solital\Core\Console\Output;

enum ColorsEnum: string
{
        // Reset all
    case RESET = "\e[0m";
    case CUSTOM = "\033[38;5;";
    case BG_CUSTOM = "\033[48;5;";
    case CLEAR = "\033[2K\r";

        // Attributes
    case BOLD = "\033[1m";
    case UN_BOLD = "\e[21m";
    case DIM = "\e[2m";
    case UN_DIM = "\e[22m";
    case ITALIC = "\e[3m";
    case UNDERLINED = "\e[4m";
    case UN_UNDERLINED = "\e[24m";
    case BLINK = "\e[5m";
    case UN_BLINK = "\e[25m";
    case REVERSE = "\e[7m";
    case UN_REVERSE = "\e[27m";
    case HIDDEN = "\e[8m";
    case UN_HIDDEN = "\e[28m";
    case STRIKETHROUGH = "\e[9m";

        // Forground colors (Warning: some include bold / unbold)
    case BLACK = "\033[0;30m";
    case DARK_GRAY = "\033[1;30m";
    case RED = "\033[0;31m";
    case GREEN = "\033[0;32m";
    case YELLOW = "\033[0;33m";
    case BLUE = "\033[0;34m";
    case MAGENTA = "\033[0;35m";
    case CYAN = "\033[0;36m";
    case WHITE = "\033[0;38m";
    case FG_DEFAULT = "\033[39m";
    case GRAY = "\033[0;90m";
    case LIGHT_GRAY = "\033[2;37m";
    case LIGHT_RED = "\033[91m";
    case LIGHT_GREEN = "\033[92m";
    case LIGHT_YELLOW = "\033[93m";
    case LIGHT_BLUE = "\033[94m";
    case LIGHT_MAGENTA = "\033[95m";
    case LIGHT_CYAN = "\033[96m";
    case LIGHT_WHITE = "\033[97m";

        // Backgrounds
    case BG_WHITE = "\e[107m";
    case BG_BLACK = "\033[40m";
    case BG_RED = "\033[41m";
    case BG_GREEN = "\033[30;42m";
    case BG_YELLOW = "\033[30;43m";
    case BG_BLUE = "\033[44m";
    case BG_MAGENTA = "\033[45m";
    case BG_CYAN = "\033[46m";
    case BG_DEFAULT = "\033[49m";
    case BG_DARK_GRAY = "\e[100m";
    case BG_LIGHT_GRAY = "\033[30;47m";
    case BG_LIGHT_RED = "\e[101m";
    case BG_LIGHT_GREEN = "\e[30;102m";
    case BG_LIGHT_YELLOW = "\e[30;103m";
    case BG_LIGHT_BLUE = "\e[104m";
    case BG_LIGHT_MAGENTA = "\e[105m";
    case BG_LIGHT_CYAN = "\e[30;106m";
}
