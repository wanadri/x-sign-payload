<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Frameworks\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Wanadri\XSignPayload\Core\SecretGenerator;

class GenerateSecret extends BaseCommand
{
    protected $group = 'xsign';
    protected $name = 'xsign:generate-secret';
    protected $description = 'Generate a secure secret key for x-sign-payload';

    public function run(array $params)
    {
        $bytes = $params[0] ?? 32;
        $secret = SecretGenerator::generate((int) $bytes);

        CLI::write('Generated secret key:', 'green');
        CLI::write($secret, 'yellow');
        CLI::write('');
        CLI::write('Add this to your app/Config/XSign.php file', 'blue');
        CLI::write("public string \$secret = '{$secret}';", 'yellow');

        return 0;
    }
}
