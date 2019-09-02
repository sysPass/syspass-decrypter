<?php
/**
 * syspass-decrypter
 *
 * @author    nuxsmin
 * @link      https://syspass.org
 * @copyright 2019-2019, Rubén Domínguez nuxsmin@$syspass.org
 *
 * This file is part of syspass-decrypter.
 *
 * syspass-decrypter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * syspass-decrypter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 *  along with syspass-decrypter.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace SPDecrypter\Util;

/**
 * Class Version
 *
 * @package SP\Util
 */
final class Version
{
    public static function getVersionString()
    {
        return sprintf('%s-%d', implode('.', array_slice(APP_VERSION, 0, 3)), APP_VERSION[3]);
    }

    /**
     * Compare versions
     *
     * @param array|string $first
     * @param array|string $second
     *
     * @return bool True if $first is lower than $second
     */
    public static function checkVersion($first, $second)
    {
        $first = self::normalizeVersionForCompare($first);
        $second = self::normalizeVersionForCompare($second);

        if (empty($first) || empty($second)) {
            return false;
        }

        if (PHP_INT_SIZE > 4) {
            return version_compare($first, $second) === -1;
        }

        list($first, $build) = explode('.', $first, 2);
        list($upgradeVersion, $upgradeBuild) = explode('.', $second, 2);

        $versionRes = (int)$first < (int)$upgradeVersion;

        return (($versionRes && (int)$upgradeBuild === 0)
            || ($versionRes && (int)$build < (int)$upgradeBuild));
    }

    /**
     * Return a normalized version string to be compared
     *
     * @param string|array $versionIn
     *
     * @return string
     */
    public static function normalizeVersionForCompare($versionIn): string
    {
        if (!empty($versionIn)) {
            if (is_string($versionIn)) {
                list($version, $build) = explode('.', $versionIn);
            } elseif (is_array($versionIn) && count($versionIn) === 4) {
                $version = implode('', array_slice($versionIn, 0, 3));
                $build = $versionIn[3];
            } else {
                return '';
            }

            $nomalizedVersion = 0;

            foreach (str_split($version) as $key => $value) {
                $nomalizedVersion += (int)$value * (10 ** (3 - $key));
            }

            return $nomalizedVersion . '.' . $build;
        }

        return '';
    }
}