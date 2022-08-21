<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use NetBull\CoreBundle\ORM\Objects\Point as BasePoint;

class Point extends Type
{
    const POINT = 'point';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::POINT;
    }

    /**
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return "POINT";
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return BasePoint
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): BasePoint
    {
        list($longitude, $latitude) = sscanf($value, 'POINT(%f %f)');

        return new BasePoint($latitude, $longitude);
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return mixed|string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof BasePoint) {
            $value = sprintf('POINT(%F %F)', $value->getLongitude(), $value->getLatitude());
        }

        return $value;
    }

    /**
     * @return bool
     */
    public function canRequireSQLConversion(): bool
    {
        return true;
    }

    /**
     * @param $sqlExpr
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform): string
    {
        return sprintf('ST_PointFromText(%s)', $sqlExpr);
    }

    /**
     * @param $sqlExpr
     * @param $platform
     * @return string
     */
    public function convertToPHPValueSQL($sqlExpr, $platform): string
    {
        return sprintf('ST_AsText(%s)', $sqlExpr);
    }

    /**
     * @param AbstractPlatform $platform
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
