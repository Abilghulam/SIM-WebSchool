<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        $gender = $this->faker->randomElement(['L', 'P']);

        // Aman kalau user belum ada, created_by/update_by boleh null (kolom nullable)
        $userId = User::query()->inRandomOrder()->value('id');

        $employment = $this->faker->randomElement([
            'PNS', 'PPPK', 'GTY', 'GTT', 'Honorer', 'Kontrak'
        ]);

        return [
            'nip' => (string) $this->faker->unique()->numerify('##################'), // 18 digit
            'full_name' => $this->faker->name($gender === 'L' ? 'male' : 'female'),
            'gender' => $gender,
            'birth_place' => $this->faker->city(),
            'birth_date' => $this->faker->dateTimeBetween('-55 years', '-23 years')->format('Y-m-d'),
            'phone' => $this->faker->numerify('08##########'),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'employment_status' => $employment,
            'is_active' => $this->faker->boolean(85), // mayoritas aktif
            'created_by' => $userId,
            'updated_by' => $userId,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => 0]);
    }
}
