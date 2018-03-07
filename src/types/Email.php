<?php
namespace fruitstudios\linkit\types;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\LinkType;
use fruitstudios\linkit\base\LinkInterface;
use fruitstudios\linkit\models\Link;

class Email extends LinkType
{
    // Private
    // =========================================================================

    // Public
    // =========================================================================

    public $customLabel;

    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        return Craft::t('link-it', 'Email Address');
    }

    public static function defaultValue(): string
    {
        return Craft::t('link-it', 'email@domain.co.uk');
    }

    // Public Methods
    // =========================================================================

    public function getLabel()
    {
        if($this->customLabel != '')
        {
            return $this->customLabel;
        }
        return static::defaultLabel();
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['customLabel', 'string'];
        return $rules;
    }

    public function getLink($value): LinkInterface
    {
        $link = new Link();
        $link->setAttributes($value, false);
        return $link;
    }

}