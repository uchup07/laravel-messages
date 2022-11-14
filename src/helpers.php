<?php

if(!function_exists('dropdown_users_active')) {
    function dropdown_users_active() {
        $output = collect();
        $users = \App\Models\User::active()->get();

        if($users) {
            $rows = $users->map(function($item, $key) {
                return ['value' => $item->id, 'name' => $item->name, 'avatar' => $item->profile_photo_url, 'email' => $item->email];
            });
            $output = $rows;
        }

        return $output;
    }
}