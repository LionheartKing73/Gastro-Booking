<?php

use Illuminate\Database\Seeder;
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

class AutoGenerateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        factory(User::class, 10)->create();
//        $types = ['Czech', 'Italian', 'Mexican', 'Ethiopian'];
//        for ($i = 0; $i < count($types); $i++){
//            factory(RestaurantType::class)->create(['name' => $types[$i]]);
//        }
//        factory(Restaurant::class, 100)->create();
//        factory(KitchenType::class, 10)->create();
//        factory(CookStyle::class, 10)->create();
//        factory(PublicMenu::class, 50)->create();
//        factory(MenuType::class, 10)->create();
//        factory(MenuGroup::class, 50)->create();
//        factory(MenuSubGroup::class, 50)->create();
//        factory(MenuList::class, 100)->create();
//        factory(MenuSchedule::class, 100)->create();
//        factory(User::class, 'client', 100)->create();
//        factory(User::class, 'restaurant', 100)->create();
//        foreach (User::all() as $user) {
//            if ($user->profile_type === "client"){
//                factory(Client::class, 1)->create(["ID_user" => $user->id, "name" => $user->name, "email" => $user->email]);
//            }
//            else if ($user->profile_type === "restaurant"){
//                factory(Restaurant::class, 2)->create(["ID_user" => $user->id]);
//            }
//        }
        $days = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];
        foreach ($days as $day) {
            factory(RestaurantOpen::class, 1)->create(["date", $day]);
        }

    }
}
