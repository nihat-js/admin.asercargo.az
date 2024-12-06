<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeInterfaceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:interface {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new interface';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');
        $interfacePath = app_path("Contracts/{$name}.php");

        if (file_exists($interfacePath)) {
            $this->error('Interface already exists!');
            return;
        }

        file_put_contents($interfacePath, "<?php\n\nnamespace App\Contracts;\n\ninterface {$name}\n{\n    // Your interface definition here\n}\n");

        $this->info("Interface created successfully: $interfacePath");
    }
}
