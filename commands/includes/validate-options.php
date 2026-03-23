<?php

/**
 * Parse CLI arguments via getopt and reject unknown --options.
 *
 * @param  string[]  $definedOptions  getopt definitions, e.g. ['token::', 'echo', 'help']
 * @return array<string, string|bool> parsed options
 *
 * Exits with code 1 if any unknown --option is found in $_SERVER['argv'].
 */
function parseCommandOptions(array $definedOptions): array
{
    $options = getopt('', $definedOptions);

    $knownNames = array_map(
        fn (string $opt) => preg_replace('/[:]+$/', '', $opt),
        $definedOptions,
    );

    foreach ($_SERVER['argv'] as $arg) {
        if (!str_starts_with($arg, '--')) {
            continue;
        }
        $name = explode('=', substr($arg, 2), 2)[0];
        $nameWithoutValue = rtrim($name, ':');
        if (!in_array($nameWithoutValue, $knownNames, true)) {
            echo "Error: Unknown option --{$nameWithoutValue}\n";

            exit(1);
        }
    }

    return $options;
}
