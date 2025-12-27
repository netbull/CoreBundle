<?php

namespace NetBull\CoreBundle\Validator\Constraints;

use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Validator\Constraint;

class PhoneNumber extends Constraint
{
    const ANY = 'any';
    const FIXED_LINE = 'fixed_line';
    const MOBILE = 'mobile';
    const PAGER = 'pager';
    const PERSONAL_NUMBER = 'personal_number';
    const PREMIUM_RATE = 'premium_rate';
    const SHARED_COST = 'shared_cost';
    const TOLL_FREE = 'toll_free';
    const UAN = 'uan';
    const VOIP = 'voip';
    const VOICEMAIL = 'voicemail';

    public ?string $message = null;
    public string $type = self::ANY;
    public string $defaultRegion = PhoneNumberUtil::UNKNOWN_REGION;
    public array $defaultRegions = [];

    public function __construct(string $defaultRegion = null, array $defaultRegions = [], $type = null, ?string $message = null, ?array $groups = null, mixed $payload = null, ?array $options = null)
    {
        parent::__construct($options, $groups, $payload);

        $this->defaultRegion = $defaultRegion ?? $this->defaultRegion;
        $this->defaultRegions = $defaultRegions ?? $this->defaultRegions;
        $this->type = $type ?? $this->type;
        $this->message = $message ?? $this->message;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return match ($this->type) {
            self::FIXED_LINE, self::MOBILE, self::PAGER, self::PERSONAL_NUMBER, self::PREMIUM_RATE, self::SHARED_COST, self::TOLL_FREE, self::UAN, self::VOIP, self::VOICEMAIL => $this->type,
            default => self::ANY,
        };

    }

    /**
     * @return string|null
     */
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
