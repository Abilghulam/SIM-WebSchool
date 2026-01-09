<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $gender = $this->faker->randomElement(['L', 'P']);

        return [
            'nis' => (string) $this->faker->unique()->numerify('2024####'),
            'full_name' => $this->faker->name($gender === 'L' ? 'male' : 'female'),
            'gender' => $gender,
            'birth_place' => $this->faker->city(),
            'birth_date' => $this->faker->dateTimeBetween('-18 years', '-15 years')->format('Y-m-d'),
            'religion' => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
            'phone' => $this->faker->numerify('08##########'),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),

            'father_name' => $this->faker->name('male'),
            'mother_name' => $this->faker->name('female'),
            'guardian_name' => $this->faker->boolean(15) ? $this->faker->name() : null,
            'parent_phone' => $this->faker->numerify('08##########'),

            'status' => 'aktif',
            'entry_year' => 2024,

            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
