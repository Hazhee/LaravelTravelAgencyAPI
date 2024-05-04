<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating a new user.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Enter the name of the user.');
        $email = $this->ask('Enter the email of the user.');
        $password = $this->secret('Enter the password of the user.');
        $roleName = $this->choice('Select the role of the user.', ['admin', 'editor'], 1);
        $role = Role::where('name', $roleName)->first();
        if (! $role) {
            $this->error('Role not found.');

            return -1;
        }

        $newUser = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'role_id' => $role->id,
        ]);

        // $validated = $newUser->validate(
        //     [
        //         'name' => 'required|string|max:255',
        //         'email' => 'required|string|email|max:255|unique:users',
        //         'password' => 'required|string|min:8',
        //         'role_id' => 'required|exists:roles,id'
        //     ]
        // );

        // if($validated->fails()) {
        //     $this->error($validated->errors()->first());
        //     return -1;
        // }

        DB::transaction(function () use ($newUser, $role) {
            $newUser->roles()->attach($role->id, ['id' => (string) Str::uuid()]);
            $this->info('User created successfully.');
        });

    }
}
