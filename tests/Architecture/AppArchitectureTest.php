<?php

arch('app has no debug helpers')
    ->expect('App')
    ->not->toUse(['dd', 'dump', 'var_dump', 'ray', 'die']);

arch('all controllers extend nothing unexpected')
    ->expect('App\Http\Controllers')
    ->toExtend('App\Http\Controllers\Controller');

arch('all models use HasUuids or have an id property')
    ->expect('App\Central\Models')
    ->toBeClasses();

arch('all notifications are queueable')
    ->expect('App\Notifications')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

arch('all jobs implement ShouldQueue')
    ->expect('App\Central\Jobs')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

arch('all enums are backed enums')
    ->expect('App\Central\Enums')
    ->toBeEnums();

arch('policies live in the Policies namespace')
    ->expect('App\Policies')
    ->toBeClasses()
    ->toHaveSuffix('Policy');

arch('form requests extend FormRequest')
    ->expect('App\Http\Requests')
    ->toExtend('Illuminate\Foundation\Http\FormRequest');

arch('api resources extend JsonResource')
    ->expect('App\Http\Resources')
    ->toExtend('Illuminate\Http\Resources\Json\JsonResource');

arch('console commands extend Command')
    ->expect('App\Console\Commands')
    ->toExtend('Illuminate\Console\Command');

arch('middleware handle method exists')
    ->expect('App\Http\Middleware')
    ->toBeClasses();

arch('services are plain classes without framework coupling')
    ->expect([
        'App\Admin\Services',
        'App\Auth\Services',
        'App\Billing\Services',
        'App\Tenant\Services',
    ])
    ->not->toUse('Illuminate\Http\Request');
