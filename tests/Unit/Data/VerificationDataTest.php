<?php

use JordanPartridge\GithubClient\Data\VerificationData;

it('can create VerificationData from array with verified signature', function () {
    $data = [
        'verified' => true,
        'reason' => 'valid',
        'signature' => '-----BEGIN PGP SIGNATURE-----\ntest\n-----END PGP SIGNATURE-----',
        'payload' => 'tree abc123\nparent def456\nauthor Test User',
        'verified_at' => '2024-01-15T10:00:00Z',
    ];

    $verification = VerificationData::fromArray($data);

    expect($verification->verified)->toBeTrue();
    expect($verification->reason)->toBe('valid');
    expect($verification->signature)->toContain('PGP SIGNATURE');
    expect($verification->payload)->toContain('tree abc123');
    expect($verification->verified_at)->toBe('2024-01-15T10:00:00Z');
});

it('can convert VerificationData to array', function () {
    $verification = new VerificationData(
        verified: true,
        reason: 'valid',
        signature: 'gpg-signature',
        payload: 'commit-payload',
        verified_at: '2024-01-15T10:00:00Z',
    );

    $array = $verification->toArray();

    expect($array)->toBe([
        'verified' => true,
        'reason' => 'valid',
        'signature' => 'gpg-signature',
        'payload' => 'commit-payload',
        'verified_at' => '2024-01-15T10:00:00Z',
    ]);
});

it('handles unverified signature', function () {
    $data = [
        'verified' => false,
        'reason' => 'unsigned',
    ];

    $verification = VerificationData::fromArray($data);

    expect($verification->verified)->toBeFalse();
    expect($verification->reason)->toBe('unsigned');
    expect($verification->signature)->toBeNull();
    expect($verification->payload)->toBeNull();
    expect($verification->verified_at)->toBeNull();
});

it('handles various verification reasons', function () {
    $reasons = [
        'valid',
        'unsigned',
        'unknown_key',
        'bad_email',
        'unknown_signature_type',
        'no_user',
        'unverified_email',
        'bad_cert',
        'not_signing_key',
        'expired_key',
        'ocsp_revoked',
    ];

    foreach ($reasons as $reason) {
        $data = [
            'verified' => $reason === 'valid',
            'reason' => $reason,
        ];

        $verification = VerificationData::fromArray($data);
        expect($verification->reason)->toBe($reason);
    }
});

it('handles gpg_reject reason', function () {
    $data = [
        'verified' => false,
        'reason' => 'gpg_reject',
        'signature' => 'bad-signature',
        'payload' => null,
    ];

    $verification = VerificationData::fromArray($data);

    expect($verification->verified)->toBeFalse();
    expect($verification->reason)->toBe('gpg_reject');
    expect($verification->signature)->toBe('bad-signature');
});

it('handles null optional fields', function () {
    $data = [
        'verified' => true,
        'reason' => 'valid',
    ];

    $verification = VerificationData::fromArray($data);

    expect($verification->signature)->toBeNull();
    expect($verification->payload)->toBeNull();
    expect($verification->verified_at)->toBeNull();
});
