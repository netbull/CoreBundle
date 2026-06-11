<?php

namespace NetBull\CoreBundle\Validator\Constraints;

use Attribute;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class PhoneNumber extends Constraint
{
    public const ANY = 'any';

    public const FIXED_LINE = 'fixed_line';

    public const MOBILE = 'mobile';

    public const PAGER = 'pager';

    public const PERSONAL_NUMBER = 'personal_number';

    public const PREMIUM_RATE = 'premium_rate';

    public const SHARED_COST = 'shared_cost';

    public const TOLL_FREE = 'toll_free';

    public const UAN = 'uan';

    public const VOIP = 'voip';

    public const VOICEMAIL = 'voicemail';

    public ?string $message = null;

    public string $type = self::ANY;

    public string $defaultRegion = PhoneNumberUtil::UNKNOWN_REGION;

    public array $defaultRegions = [];

    public function __construct(?string $defaultRegion = null, array $defaultRegions = [], $type = null, ?string $message = null, ?array $groups = null, mixed $payload = null, ?array $options = null)
    {
        parent::__construct($options, $groups, $payload);

        $this->defaultRegion = $defaultRegion ?? $this->defaultRegion;
        $this->defaultRegions = $defaultRegions ?? $this->defaultRegions;
        $this->type = $type ?? $this->type;
        $this->message = $message ?? $this->message;
    }

    public function getType(): string
    {
        return match ($this->type) {
            self::FIXED_LINE, self::MOBILE, self::PAGER, self::PERSONAL_NUMBER, self::PREMIUM_RATE, self::SHARED_COST, self::TOLL_FREE, self::UAN, self::VOIP, self::VOICEMAIL => $this->type,
            default => self::ANY,
        };
    }

    public function getMessage(): ?string
    {
        if (null !== $this->message) {
            return $this->message;
        }

        switch ($this->type) {
            case self::FIXED_LINE:
                return 'This value is not a valid fixed-line number.';
            case self::MOBILE:
                return 'This value is not a valid mobile number.';
            case self::PAGER:
                return 'This value is not a valid pager number.';
            case self::PERSONAL_NUMBER:
                return 'This value is not a valid personal number.';
            case self::PREMIUM_RATE:
                return 'This value is not a valid premium-rate number.';
            case self::SHARED_COST:
                return 'This value is not a valid shared-cost number.';
            case self::TOLL_FREE:
                return 'This value is not a valid toll-free number.';
            case self::UAN:
                return 'This value is not a valid UAN.';
            case self::VOIP:
                return 'This value is not a valid VoIP number.';
            case self::VOICEMAIL:
                return 'This value is not a valid voicemail access number.';
        }

        return 'This value is not a valid phone number.';
    }
}
