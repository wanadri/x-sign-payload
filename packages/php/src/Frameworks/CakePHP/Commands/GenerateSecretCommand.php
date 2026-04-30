<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Frameworks\CakePHP\Commands;

use Cake\Console\Arguments;
use Cake\Console\BaseCommand;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Wanadri\XSignPayload\Core\SecretGenerator;

class GenerateSecretCommand extends BaseCommand
{
    public static function defaultName(): string
    {
        return 'xsign generate-secret';
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $bytes = $args->getOption('bytes') ?? 32;
        $secret = SecretGenerator::generate((int) $bytes);

        $io->success('Generated secret key:');
        $io->out($secret);
        $io->out('');
        $io->info('Add this to your config/x_sign_payload.php file');

        return static::CODE_SUCCESS;
    }

    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('Generate a secure secret key for x-sign-payload');
        $parser->addOption('bytes', [
            'short' => 'b',
            'help' => 'Number of random bytes to generate',
            'default' => 32,
        ]);

        return $parser;
    }
}
