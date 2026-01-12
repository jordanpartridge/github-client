<?php

use Carbon\Carbon;
use JordanPartridge\GithubClient\Data\Commits\CommitAuthorData;

it('can create CommitAuthorData from array', function () {
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'date' => '2024-01-15T10:30:00Z',
    ];

    $author = CommitAuthorData::fromArray($data);

    expect($author->name)->toBe('John Doe');
    expect($author->email)->toBe('john@example.com');
    expect($author->date)->toBeInstanceOf(Carbon::class);
    expect($author->date->toISOString())->toBe('2024-01-15T10:30:00.000000Z');
});

it('can convert CommitAuthorData to array', function () {
    $date = Carbon::parse('2024-01-15T10:30:00Z');

    $author = new CommitAuthorData(
        name: 'Jane Doe',
        email: 'jane@example.com',
        date: $date,
    );

    $array = $author->toArray();

    expect($array['name'])->toBe('Jane Doe');
    expect($array['email'])->toBe('jane@example.com');
    expect($array['date'])->toBe($date->toISOString());
});

it('parses different date formats correctly', function () {
    $dates = [
        '2024-01-15T10:30:00Z',
        '2024-01-15 10:30:00',
        '2024-01-15',
    ];

    foreach ($dates as $dateString) {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'date' => $dateString,
        ];

        $author = CommitAuthorData::fromArray($data);
        expect($author->date)->toBeInstanceOf(Carbon::class);
    }
});
