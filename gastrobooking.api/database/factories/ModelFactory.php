<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use App\User;
use App\Entities\Restaurant;
use App\Entities\RestaurantOpen;
use App\Entities\RestaurantType;
use App\Entities\MenuCookStyle;
use App\Entities\MenuDiet;
use App\Entities\MenuGroup;
use App\Entities\MenuIngredient;
use App\Entities\MenuList;
use App\Entities\MenuSchedule;
use App\Entities\MenuSubGroup;
use App\Entities\MenuType;
use App\Entities\Diet;
use App\Entities\PublicMenu;
use App\Entities\KitchenType;
use App\Entities\CookStyle;
use App\Entities\Client;
use App\Entities\ClientGroup;

$factory->define(User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => \Illuminate\Support\Facades\Hash::make("123"),
    ];
});
$factory->defineAs(User::class, "client", function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => \Illuminate\Support\Facades\Hash::make("123"),
        'profile_type' => "client"
    ];
});
$factory->defineAs(User::class, "restaurant", function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => \Illuminate\Support\Facades\Hash::make("123"),
        'profile_type' => "restaurant"
    ];
});
$factory->define(Client::class, function (Faker\Generator $faker) {
    return [
        'ID_diet' => 1,
        'email_new' => $faker->numberBetween(0, 1),
        'email_update' => $faker->numberBetween(0, 1),
        'email_restaurant_update' => $faker->numberBetween(0, 1),
    ];
});
$factory->define(RestaurantOpen::class, function (Faker\Generator $faker) {
    return [
        'ID_restaurant' => 92,
        'm_starting_time' => "08:00",
        "m_ending_time" => "12:00",
        "a_starting_time" => "15:00",
        "a_ending_time" => "22:00"

    ];
});
$factory->define(ClientGroup::class, function (Faker\Generator $faker) {
    $approved = ['R', 'Y', 'N', 'D'];
    return [
        'ID_grouped_client' => $faker->numberBetween(1, 100),
        'approved' => $approved[array_rand($approved, 1)]
    ];
});

$factory->define(Restaurant::class, function(Faker\Generator $faker){
    return [
        'ID_restaurant_type' => $faker->numberBetween(1, 4),
        'name' => $faker->company,
        'email' => $faker->companyEmail,
        'phone' => $faker->phoneNumber,
        'street' => $faker->streetName,
        'city' => $faker->city,
        'post_code' => $faker->postcode,
        'address_note' => $faker->address,
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
        'company_number' => $faker->randomNumber(4),
        'company_tax_number' => $faker->bankAccountNumber,
        'account_number' => $faker->bankAccountNumber,
        'short_descr' => $faker->sentence,
        'long_descr' => $faker->text,
    ];
});

$factory->define(RestaurantType::class, function(Faker\Generator $faker){
    return [
        'description' => \Carbon\Carbon::now()
    ];
});

$factory->define(CookStyle::class, function(Faker\Generator $faker){
    return [
        'name' => $faker->word,
        'cust_order' => $faker->randomNumber(3),
        'lang' => $faker->languageCode,
        'public' => $faker->randomNumber(3)
    ];
});

$factory->define(KitchenType::class, function(Faker\Generator $faker){
    return [
        'name' => $faker->word,
        'lang' => $faker->languageCode
    ];
});

$factory->define(PublicMenu::class, function(Faker\Generator $faker){
    return [
        'ID_kitchen_type' => $faker->numberBetween(1, 10),
        'ID_cook_style' => $faker->numberBetween(1, 10),
        'ID_public_menu_group' => 1,
        'name' => $faker->word,
        'prefix' => $faker->countryCode,
        'lang' => $faker->languageCode,
        'instruction' => $faker->text
    ];
});

$factory->define(MenuType::class, function(Faker\Generator $faker){
    return [
        'name' => $faker->word,
        'cust_order' => $faker->randomNumber(3),
        'lang' => $faker->languageCode,
        'public' => $faker->randomNumber(3)
    ];
});

$factory->define(MenuGroup::class, function(Faker\Generator $faker){
    return [
        'ID_menu_type' => $faker->numberBetween(1, 10),
        'name' => $faker->word,
        'cust_order' => $faker->randomNumber(3),
        'lang' => $faker->languageCode,
        'public' => $faker->randomNumber(3)
    ];
});

$factory->define(MenuSubGroup::class, function(Faker\Generator $faker){
    return [
        'ID_menu_group' => $faker->numberBetween(1, 10),
        'name' => $faker->word,
        'cust_order' => $faker->randomNumber(3),
        'lang' => $faker->languageCode,
        'public' => $faker->randomNumber(3)
    ];
});

$factory->define(MenuList::class, function(Faker\Generator $faker){
    return [
        'ID_restaurant' => $faker->numberBetween(1, 100),
        'ID_public_menu' => $faker->numberBetween(1, 50),
        'ID_menu_group' => $faker->numberBetween(1, 50),
        'ID_menu_subgroup' => $faker->numberBetween(1, 50),
        'ID_kitchen_type' => $faker->numberBetween(1, 10),
        'prefix' => $faker->countryCode,
        'name' => $faker->word,
        'is_day_menu' => random_int(0, 1),
        'time_from' => \Carbon\Carbon::yesterday(),
        'time_to' => \Carbon\Carbon::tomorrow(),
        'price' => $faker->randomNumber(3),
        'comment' => $faker->text

    ];
});

$factory->define(MenuSchedule::class, function(Faker\Generator $faker){
    return [
        'ID_menu_list' => $faker->numberBetween(1, 100),
        'datetime_from' => \Carbon\Carbon::yesterday(),
        'datetime_to' => \Carbon\Carbon::tomorrow(),
        'max_portions' => $faker->randomNumber(5)

    ];
});
