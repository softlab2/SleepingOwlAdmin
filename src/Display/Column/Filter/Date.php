<?php

namespace SleepingOwl\Admin\Display\Column\Filter;

use Exception;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use SleepingOwl\Admin\Contracts\RepositoryInterface;
use SleepingOwl\Admin\Contracts\NamedColumnInterface;

class Date extends Text
{
    /**
     * @var string
     */
    protected $view = 'column.filter.date';

    /**
     * @var string
     */
    protected $format;

    /**
     * @var string
     */
    protected $pickerFormat;

    /**
     * @var string
     */
    protected $searchFormat = 'Y-m-d';

    /**
     * @var bool
     */
    protected $seconds = false;

    /**
     * @var int
     */
    protected $width = 150;

    public function initialize()
    {
        parent::initialize();
        $this->setHtmlAttribute('data-type', 'date');
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     *
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSeconds()
    {
        return $this->seconds;
    }

    /**
     * @param bool $seconds
     *
     * @return $this
     */
    public function setSeconds($seconds)
    {
        $this->seconds = (bool) $seconds;

        return $this;
    }

    /**
     * @return string
     */
    public function getPickerFormat()
    {
        return $this->pickerFormat ?: config('sleeping_owl.dateFormat');
    }

    /**
     * @param string $pickerFormat
     *
     * @return $this
     */
    public function setPickerFormat($pickerFormat)
    {
        $this->pickerFormat = $pickerFormat;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     *
     * @return $this
     */
    public function setWidth($width)
    {
        intval($width);

        if ($width < 0) {
            $width = 0;
        }

        $this->width = (int) $width;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchFormat()
    {
        return $this->searchFormat;
    }

    /**
     * @param string $searchFormat
     *
     * @return $this
     */
    public function setSearchFormat($searchFormat)
    {
        $this->searchFormat = $searchFormat;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $format = $this->getPickerFormat();
        if (empty($format)) {
            $format = $this->getFormat();
        }

        return parent::toArray() + [
            'seconds'      => $this->hasSeconds(),
            'format'       => $this->getFormat(),
            'pickerFormat' => $this->generatePickerFormat(
                $this->getPickerFormat()
            ),
            'width'        => $this->getWidth(),
        ];
    }

    /**
     * @param string $format
     *
     * @return string
     */
    protected function generatePickerFormat($format)
    {
        return strtr($format, [
            'i' => 'mm',
            's' => 'ss',
            'h' => 'hh',
            'H' => 'HH',
            'g' => 'h',
            'G' => 'H',
            'd' => 'DD',
            'j' => 'D',
            'm' => 'MM',
            'n' => 'M',
            'Y' => 'YYYY',
            'y' => 'YY',
        ]);
    }

    /**
     * @param string $date
     *
     * @return string
     */
    public function parseValue($date)
    {
        if (empty($date)) {
            return;
        }

        try {
            $date = Carbon::parse($date);
        } catch (Exception $e) {
            try {
                $date = Carbon::createFromFormat($this->getPickerFormat(), $date);
            } catch (Exception $e) {
                return;
            }
        }

        return $date->format($this->getSearchFormat());
    }
}
