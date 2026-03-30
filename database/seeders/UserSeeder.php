<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Gọi Model User vào
use Illuminate\Support\Facades\Hash; // Gọi thư viện mã hóa mật khẩu

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo tài khoản Admin cấp cao
        User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'), // Mật khẩu bắt buộc phải mã hóa Hash
        ]);
    }
}