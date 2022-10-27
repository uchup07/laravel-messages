<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'MessageController@index')->name('message.index');
Route::get('create', 'MessageController@create')->name('message.create');
Route::post('store', 'MessageController@store')->name('message.store');
Route::post('{thread}/reply', 'MessageController@reply')->name('message.reply');
Route::get('{thread}', 'MessageController@show')->name('message.show');
Route::delete('{thread}/destroy', 'MessageController@destroy')->name('message.destroy');


