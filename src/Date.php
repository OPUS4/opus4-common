<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @copyright   Copyright (c) 2022, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Common;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use Opus\Common\Model\AbstractModel;
use Opus\Common\Model\Field;
use Opus\Common\Model\ModelException;
use Opus\Common\Security\SecurityException;

use function checkdate;
use function get_class;
use function gmdate;
use function htmlspecialchars;
use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function preg_match;
use function sprintf;
use function substr;

/**
 * TODO LAMINAS validate all field values except for Timezone as Integer
 * TODO LAMINAS Why SecurityException?
 */
class Date extends AbstractModel
{
    public const FIELD_YEAR      = 'Year';
    public const FIELD_MONTH     = 'Month';
    public const FIELD_DAY       = 'Day';
    public const FIELD_HOUR      = 'Hour';
    public const FIELD_MINUTE    = 'Minute';
    public const FIELD_SECOND    = 'Second';
    public const FIELD_TIMEZONE  = 'Timezone';
    public const FIELD_TIMESTAMP = 'UnixTimestamp';

    /**
     * Regular expression for complete time string.
     */
    public const TIMEDATE_REGEXP =
        '/^(\d{1,4})-(\d{1,2})-(\d{1,2})T(\d{1,2}):(\d{1,2}):(\d{1,2})([A-Za-z]+|[+-][\d:]+)$/';

    /**
     * Regular expression for time string with just a date.
     */
    public const DATEONLY_REGEXP = '/^(\d{1,4})-(\d{1,2})-(\d{1,2})$/';

    public const DATETIME_FORMAT_FULL = ''; // TODO use

    public const DATETIME_FORMAT_DATE_ONLY = ''; // TODO use

    /** @var array */
    private $values = [];

    /**
     * Set up model with given value or with the current timestamp.
     *
     * @param null|DateTime|self|string $value (Optional) Some sort of date representation.
     *
     * TODO add array as option?
     */
    public function __construct($value = null)
    {
        parent::__construct();

        if ($value instanceof DateTime) {
            $this->setDateTime($value);
        } elseif (is_string($value) && preg_match(self::TIMEDATE_REGEXP, $value)) {
            $this->setFromString($value);
        } elseif (is_string($value) && preg_match(self::DATEONLY_REGEXP, $value)) {
            $this->setFromString($value);
        } elseif ($value instanceof Date) {
            $this->updateFrom($value);
        } elseif (is_int($value)) {
            $this->setTimestamp($value);
        } else {
            $this->resetValues();
        }
    }

    /**
     * Resets all fields to null.
     */
    protected function resetValues()
    {
        $this->values = [
            self::FIELD_YEAR     => null,
            self::FIELD_MONTH    => null,
            self::FIELD_DAY      => null,
            self::FIELD_HOUR     => null,
            self::FIELD_MINUTE   => null,
            self::FIELD_SECOND   => null,
            self::FIELD_TIMEZONE => null,
        ];
    }

    /**
     * Returns a DateTime instance properly set up with
     * date values as described in the Models fields.
     *
     * @param string|null $timezone
     * @return DateTime|null
     */
    public function getDateTime($timezone = null)
    {
        if (! $this->isValidDate()) {
            return null;
        }

        $date = $this->__toString();
        if ($this->isDateOnly()) {
            if ($timezone !== null) {
                $date = substr($date, 0, 10) . 'T00:00:00' . $timezone;
                return DateTime::createFromFormat('Y-m-d\TH:i:se', $date);
            } else {
                $date = substr($date, 0, 10) . 'T00:00:00';
                return DateTime::createFromFormat('Y-m-d\TH:i:s', $date);
            }
        }

        $dateTime = DateTime::createFromFormat('Y-m-d\TH:i:se', $date);
        if ($timezone !== null) {
            if ($timezone === 'Z') {
                $timezone = 'UTC';
            }
            $dateTime->setTimezone(new DateTimeZone($timezone));
        }
        return $dateTime;
    }

    /**
     * Set date and time values from DateTime instance.
     *
     * @param DateTime $datetime DateTime instance to use.
     * @return $this provide fluent interface.
     */
    public function setDateTime($datetime)
    {
        if (! $datetime instanceof DateTime) {
            throw new InvalidArgumentException('Invalid DateTime object.');
        }

        $this->values[self::FIELD_YEAR]   = $datetime->format("Y");
        $this->values[self::FIELD_MONTH]  = $datetime->format("m");
        $this->values[self::FIELD_DAY]    = $datetime->format("d");
        $this->values[self::FIELD_HOUR]   = $datetime->format("H");
        $this->values[self::FIELD_MINUTE] = $datetime->format("i");
        $this->values[self::FIELD_SECOND] = $datetime->format("s");

        $timeZone                           = $datetime->format("P");
        $this->values[self::FIELD_TIMEZONE] = $timeZone === '+00:00' ? 'Z' : $timeZone;

        return $this;
    }

    /**
     * Set date values from DateTime instance; shortcut for date-setting only.
     *
     * @param DateTime $datetime DateTime instance to use.
     * @return $this Provide fluent interface.
     */
    public function setDateOnly($datetime)
    {
        $this->setDateTime($datetime);
        $this->values[self::FIELD_HOUR]     = null;
        $this->values[self::FIELD_MINUTE]   = null;
        $this->values[self::FIELD_SECOND]   = null;
        $this->values[self::FIELD_TIMEZONE] = null;

        return $this;
    }

    /**
     * Checks, if the current date object also defines time/time zone values.
     *
     * @return bool
     */
    public function isDateOnly()
    {
        return $this->values[self::FIELD_HOUR] === null
            || $this->values[self::FIELD_MINUTE] === null
            || $this->values[self::FIELD_SECOND] === null
            || $this->values[self::FIELD_TIMEZONE] === null;
    }

    /**
     * Set up date model from string representationo of a date.
     * Date parsing depends on current set locale date format.
     *
     * @param  string $date Date string to set.
     */
    public function setFromString($date)
    {
        if (true === empty($date)) {
            throw new InvalidArgumentException('Empty date string passed.');
        }

        if (preg_match(self::TIMEDATE_REGEXP, $date)) {
            $datetime = DateTime::createFromFormat('Y-m-d\TH:i:se', $date);
            $this->setDateTime($datetime);
        } elseif (preg_match(self::DATEONLY_REGEXP, $date)) {
            $date     = substr($date, 0, 10) . 'T00:00:00Z';
            $datetime = DateTime::createFromFormat('Y-m-d\TH:i:se', $date);
            $this->setDateOnly($datetime);
        } else {
            throw new InvalidArgumentException('Invalid date-time string.');
        }
    }

    /**
     * Set the current date, time and timezone.
     */
    public function setNow()
    {
        $this->setDateTime(new DateTime());
    }

    /**
     * Creates Opus\Date object set to the time of creation.
     *
     * @return self
     */
    public static function getNow()
    {
        $date = new Date();
        $date->setNow();
        return $date;
    }

    /**
     * Return ISO 8601 string representation of the date.  For instance:
     *    2011-02-28T23:59:59[+-]01:30
     *    2011-02-28T23:59:59(Z|UTC|...)
     *    2011-02-28                    (if some time values/time zone are null)
     *
     * @return string ISO 8601 date string.
     *
     * TODO how to deal with invalid
     */
    public function __toString()
    {
        if (! $this->isValid()) {
            return '';
        }
        $dateStr = sprintf(
            '%04d-%02d-%02d',
            $this->values[self::FIELD_YEAR],
            $this->values[self::FIELD_MONTH],
            $this->values[self::FIELD_DAY]
        );
        if ($this->isDateOnly()) {
            return $dateStr;
        }

        $timeStr = sprintf(
            '%02d:%02d:%02d',
            $this->values[self::FIELD_HOUR],
            $this->values[self::FIELD_MINUTE],
            $this->values[self::FIELD_SECOND]
        );
        $tzStr   = $this->values[self::FIELD_TIMEZONE];
        return $dateStr . "T" . $timeStr . $tzStr;
    }

    /**
     * Returns string matching Zend_Date::getIso format.
     *
     * @return string
     *
     * TODO should this format be changed - Does it differ from __toString
     */
    public function getIso()
    {
        return $this->getDateTime()->format(DateTime::RFC3339);
    }

    /**
     * Overload isValid to for additional date checks.
     *
     * @return bool
     *
     * TODO validate values except timezone as integers
     */
    public function isValid()
    {
        return $this->isValidDate();
    }

    /**
     * Checks if date is valid.
     *
     * This function is used because the regular isValid function calls the parent::isValid function which checks the
     * values of all the fields which leads to an endless recursion.
     *
     * @return bool
     */
    public function isValidDate()
    {
        $month = $this->values[self::FIELD_MONTH];
        $day   = $this->values[self::FIELD_DAY];
        $year  = $this->values[self::FIELD_YEAR];

        if ($month === null || $day === null || $year === null) {
            return false;
        } else {
            return checkdate($month, $day, $year);
        }
    }

    /**
     * Synchronize dependent fields.
     *
     * @param array|string|int $data
     *
     * TODO If multiple values are set the unix timestamp is updated several times. It might make more sense to
     *      generate it on demand.
     */
    public function updateFromArray($data)
    {
        if (is_array($data)) {
            $this->resetValues(); // TODO is that the desired behavior?
            parent::updateFromArray($data);
        } else {
            if (is_int($data)) {
                $this->setTimestamp($data);
            } else {
                $this->setFromString($data);
            }
        }
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function updateValue($name, $value)
    {
        $this->values[$name] = $value;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getValue($name)
    {
        return $this->values[$name];
    }

    public function clear()
    {
        $this->resetValues();
    }

    /**
     * Compares to another Opus\Common\Date objekt.
     *
     * @param self $date Date object
     * @return int
     * @throws ModelException
     */
    public function compare($date)
    {
        if ($date === null) {
            // a date is always "larger than" null
            return 1;
        }

        if (! $date instanceof Date) {
            $class     = self::class;
            $dateClass = get_class($date);
            throw new ModelException("Cannot compare $dateClass with $class object.");
        }

        $thisDateTime = $this->getDateTime('Z');

        if ($thisDateTime === null) {
            $dateStr = htmlspecialchars($this->__toString());
            throw new ModelException("Date '$dateStr' is invalid.");
        }

        $dateTime = $date->getDateTime('Z');

        if ($dateTime === null) {
            $dateStr = htmlspecialchars($date->__toString());
            throw new ModelException("Date '$dateStr' is invalid.");
        }

        $thisTimestamp = $thisDateTime->getTimestamp();
        $timestamp     = $dateTime->getTimestamp();

        if ($thisTimestamp === $timestamp) {
            return 0; // equal
        } elseif ($thisTimestamp < $timestamp) {
            return -1; // less than
        } else {
            return 1; // larger than
        }
    }

    /**
     * Checks if given date is later.
     *
     * @param self $date
     * @return bool True if given date is later than parameter date
     * @throws ModelException
     */
    public function isLater($date)
    {
        return $this->compare($date) === 1;
    }

    /**
     * @return int|null
     */
    public function getUnixTimestamp()
    {
        return $this->getTimestamp();
    }

    /**
     * Returns a UNIX timestamp if the value is valid.
     *
     * The UnixTimestamp field may return null if only a date is stored, but not a time.
     *
     * @return int|null
     */
    public function getTimestamp()
    {
        $dateTime = $this->getDateTime('Z');
        if ($dateTime !== null) {
            return $dateTime->getTimestamp();
        } else {
            return null;
        }
    }

    /**
     * Updates all values for the provided timestamp and UTC.
     *
     * UTC is used, because the string presentation of a timestamp depends on the time zone.
     *
     * @param int $value
     *
     * TODO extend function to provide time zone as second parameter?
     */
    public function setTimestamp($value)
    {
        if ($value === null) {
            $this->clear();
        } else {
            $dateTime = gmdate('Y-m-d\TH:i:s\Z', $value);
            $this->setFromString($dateTime);
        }
    }

    /**
     * Function declared here to mark it as deprecated.
     *
     * The UnixTimestamp is a read-only field and should not be set.
     *
     * @deprecated
     *
     * @param int $value
     * @throws ModelException
     * @throws SecurityException
     */
    public function setUnixTimestamp($value)
    {
        // parent::setUnixTimestamp($value);
    }

    /**
     * Magic method to access the models fields via virtual set/get methods.
     *
     * @param string $name      Name of the method beeing called.
     * @param array  $arguments Arguments for function call.
     * @throws InvalidArgumentException When adding a link to a field without an argument.
     * @throws ModelException     If an unknown field or method is requested.
     * @throws SecurityException  If the current role has no permission for the requested operation.
     * @return mixed Might return a value if a getter method is called.
     */
    public function __call($name, array $arguments)
    {
        $accessor  = substr($name, 0, 3);
        $fieldName = substr($name, 3);

        $argumentGiven = false;
        $argument      = null;
        if (false === empty($arguments)) {
            $argumentGiven = true;
            $argument      = $arguments[0];
        }

        // check if requested field is known
        if (! in_array($fieldName, self::describe())) {
            throw new ModelException('Unknown field: ' . $fieldName);
        }

        // check if set/add has been called with an argument
        if ((false === $argumentGiven) && ($accessor === 'set')) {
            throw new ModelException('Argument required for set() calls, none given.');
        }

        switch ($accessor) {
            case 'get':
                return $this->getValue($fieldName);

            case 'set':
                $this->updateValue($fieldName, $argument);
                return $this;

            default:
                throw new ModelException('Unknown accessor function: ' . $accessor);
        }
    }

    /**
     * @return string[]
     */
    public static function describe()
    {
        return [
            self::FIELD_YEAR,
            self::FIELD_MONTH,
            self::FIELD_DAY,
            self::FIELD_HOUR,
            self::FIELD_MINUTE,
            self::FIELD_SECOND,
            self::FIELD_TIMEZONE,
            self::FIELD_TIMESTAMP,
        ];
    }

    /**
     * @param string $name
     * @return Field
     * @throws ModelException
     */
    public function getField($name)
    {
        if (! in_array($name, $this->describe())) {
            throw new ModelException('unknown field'); // TODO LAMINAS use custom exception
        }

        return new Field($this, $name, null);
    }

    public function updateFrom(self $model)
    {
        foreach ($model->describe() as $fieldName) {
            if ($fieldName === self::FIELD_TIMESTAMP) {
                continue; // TODO read-only field (virtual)
            }
            $this->updateValue($fieldName, $model->getValue($fieldName));
        }
    }

    /**
     * @return array
     */
    protected static function loadModelConfig()
    {
        return [];
    }
}
