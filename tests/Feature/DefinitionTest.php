<?php

declare(strict_types=1);

use Guggach\Reports\Definition\Bands\Detail;
use Guggach\Reports\Definition\Bands\PageFooter;
use Guggach\Reports\Definition\ReportBuilder;

it('stores bands from the builder', function (): void {
    $builder = new ReportBuilder;

    $builder->pageHeader(closure: fn (): string => 'header')->height(15)->hideOnFirstPage();
    $builder->pageFooter(closure: fn (): string => 'footer')->height(12);
    $builder->reportStart(closure: fn (): string => 'start');
    $builder->reportEnd(closure: fn (): string => 'end');
    $builder->detail(closure: fn (): string => 'row', height: 6)
        ->repeatGridHeader(closure: fn (): string => 'grid');

    $definition = $builder->build();

    expect($definition->pageHeader)->not->toBeNull()
        ->and($definition->pageHeader->height)->toBe(15.0)
        ->and($definition->pageHeader->visibilities)->toContain('hideOnFirstPage');

    expect($definition->pageFooter?->height)->toBe(12.0);
    expect($definition->reportStart)->not->toBeNull();
    expect($definition->reportEnd)->not->toBeNull();

    expect($definition->detail)->not->toBeNull()
        ->and($definition->detail->height)->toBe(6.0)
        ->and($definition->detail->keepTogether)->toBeTrue();

    expect($definition->gridHeader())->not->toBeNull();
});

it('flattens nested groups into an ordered list with levels', function (): void {
    $builder = new ReportBuilder;

    $builder->group('category', function ($group): void {
        $group->header(closure: fn (): string => 'category-head');
        $group->sum('price', as: 'category_total');

        $group->group(['brand', 'model'], function ($group): void {
            $group->footer(closure: fn (): string => 'brand-foot');
        });
    });

    $definition = $builder->build();

    expect($definition->groups)->toHaveCount(2);

    expect($definition->groups[0]->level)->toBe(1)
        ->and($definition->groups[0]->keys)->toBe(['category'])
        ->and($definition->groups[0]->header)->not->toBeNull();

    expect($definition->groups[1]->level)->toBe(2)
        ->and($definition->groups[1]->keys)->toBe(['brand', 'model'])
        ->and($definition->groups[1]->footer)->not->toBeNull();
});

it('stores enrichment, driving source and link mapping on a group', function (): void {
    $builder = new ReportBuilder;

    $builder->group('category', function ($group): void {
        $group->enrichWith('App\\Reports\\PriceList\\CategoryInfo');
        $group->drivesWith('App\\Reports\\PriceList\\CategorySource');
        $group->link(['category_id' => 'id']);
    });

    $definition = $builder->build();

    expect($definition->groups[0]->enrichment)->toBe('App\\Reports\\PriceList\\CategoryInfo')
        ->and($definition->groups[0]->drivingSource)->toBe('App\\Reports\\PriceList\\CategorySource')
        ->and($definition->groups[0]->link)->toBe(['category_id' => 'id']);
});

it('accepts a prepared band instance for a slot (variant A)', function (): void {
    $custom = new class extends Detail
    {
        public function hint(): string
        {
            return 'custom';
        }
    };

    $custom->height(9);

    $builder = new ReportBuilder;
    $stored = $builder->detail($custom);
    $builder->pageFooter(new PageFooter, closure: fn (): string => 'override');

    $definition = $builder->build();

    expect($stored)->toBe($custom)
        ->and($custom->hint())->toBe('custom')
        ->and($definition->detail)->toBe($custom)
        ->and($definition->detail->height)->toBe(9.0);

    expect($definition->pageFooter?->closure)->not->toBeNull();
});
