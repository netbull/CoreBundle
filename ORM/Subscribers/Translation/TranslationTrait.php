<?php

namespace NetBull\CoreBundle\ORM\Subscribers\Translation;

use Knp\DoctrineBehaviors\Model\Translatable\Translation;

/**
 * Trait TranslationTrait
 * @package NetBull\CoreBundle\ORM\Subscribers\Translation
 */
trait TranslationTrait
{
    use Translation;
    
    /**
     * Tells if translation is empty
     * @return bool true if translation is not filled
     */
    public function isEmpty()
    {
        $ignore = true;
        if (isset($this->mandatoryFields)) {
            foreach ($this->mandatoryFields as $man) {
                if (!is_null($this->{$man})) {
                    $ignore = false;
                }
            }
        }

        return $ignore;
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryFields()
    {
        return $this->mandatoryFields;
    }
}
