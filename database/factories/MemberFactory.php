<?php

namespace Database\Factories;

use App\Enums\MemberStatus;
use App\Enums\PreferredTime;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->caucasianName(),
            'phone' => '+7'.$this->faker->numerify('9#########'),
            'photo_url' => null,
            'birth_date' => $this->faker->optional(0.6)->dateTimeBetween('-55 years', '-16 years')?->format('Y-m-d'),
            'preferred_time' => $this->faker->optional(0.6)->randomElement(PreferredTime::cases()),
            'note' => $this->faker->optional(0.3)->sentence(),
            'status' => MemberStatus::Active,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => MemberStatus::Pending]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => MemberStatus::Archived]);
    }

    /**
     * A random Chechen / North Caucasus male name, "Surname First".
     */
    private function caucasianName(): string
    {
        $surnames = [
            'Ахмадов', 'Исаев', 'Дудаев', 'Висаев', 'Муцаев', 'Эльмурзаев', 'Тагиров', 'Хасанов',
            'Алиев', 'Гелаев', 'Хамзатов', 'Магомадов', 'Бекмурзаев', 'Яхъяев', 'Умаров', 'Сулейманов',
            'Абдулаев', 'Закриев', 'Идрисов', 'Джабраилов', 'Батаев', 'Албаков', 'Басаев', 'Мусаев',
        ];
        $maleNames = [
            'Адлан', 'Ахмед', 'Магомед', 'Исмаил', 'Руслан', 'Хусейн', 'Салман', 'Умар',
            'Аслан', 'Беслан', 'Зелимхан', 'Тимур', 'Муса', 'Идрис', 'Рамзан', 'Ильяс',
            'Хасан', 'Лечи', 'Тамерлан', 'Эльдар', 'Мансур', 'Арби', 'Султан', 'Шамиль',
        ];

        return $this->faker->randomElement($surnames).' '.$this->faker->randomElement($maleNames);
    }
}
