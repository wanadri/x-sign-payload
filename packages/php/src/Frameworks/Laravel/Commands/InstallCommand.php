<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Frameworks\Laravel\Commands;

use Illuminate\Console\Command;
use Wanadri\XSignPayload\Core\SecretGenerator;

class InstallCommand extends Command
{
    protected $signature = 'x-sign:install';
    protected $description = 'Install x-sign-payload and generate a secret key';

    public function handle(): int
    {
        $secret = SecretGenerator::generate(32);
        
        // Add to .env file
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');
        
        $envVars = [
            'X_SIGN_SECRET' => $secret,
            'X_SIGN_ALGORITHM' => 'sha256',
            'X_SIGN_ENABLE_TIMESTAMP' => 'true',
            'X_SIGN_REPLAY_WINDOW' => '10',
        ];
        
        foreach ($envVars as $key => $value) {
            $this->writeEnvVariable($envPath, $key, $value);
            $this->writeEnvVariable($envExamplePath, $key, $value);
        }
        
        // Publish config
        $this->call('vendor:publish', [
            '--provider' => 'Wanadri\XSignPayload\Frameworks\Laravel\XSignPayloadServiceProvider',
            '--tag' => 'config',
        ]);
        
        $this->info('✅ x-sign-payload installed successfully!');
        $this->info("🔐 Generated secret: {$secret}");
        $this->info('📝 Secret added to .env file');
        $this->info('⚙️  Config published to config/x-sign-payload.php');
        
        return self::SUCCESS;
    }
    
    private function writeEnvVariable(string $path, string $key, string $value): void
    {
        if (!file_exists($path)) {
            return;
        }
        
        $content = file_get_contents($path);
        
        // Check if variable already exists
        if (preg_match("/^{$key}=/m", $content)) {
            // Update existing
            $content = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $content
            );
        } else {
            // Add new
            $content .= "\n{$key}={$value}\n";
        }
        
        file_put_contents($path, $content);
    }
}
