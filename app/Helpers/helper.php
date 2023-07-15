<?php

if (! function_exists('enum_to_array')) {
    function enum_to_array(mixed $enum): array
    {
        return array_column($enum::cases(), 'value');
    }
}
