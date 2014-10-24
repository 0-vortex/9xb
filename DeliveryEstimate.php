<?php

class DeliveryEstimate
{
    public $shippingDate = false;
    public $shippingDuration = 3;
    public $cutoff = array(
        "working" => "12",
        "non-working" => "14"
    );
    public $bankHolidays = array(
        "New Year's Day" => "01-01",
        "Good Friday" => "04-18",
        "Easter Monday" => "04-21",
        "Early May Bank Holiday" => "05-05",
        "Spring Bank Holiday" => "05-26",
        "Summer Bank Holiday" => "07-25",
        "Christmas Day" => "12-25",
        "Boxing Day" => "12-26",
    );

    public $error = false;
    public $date = false;
    public $processingDate = false;
    public $deliveryDate = false;

    public function __construct ($time, $zone = null) {
        // DateTimeZone will never be 0
        // no === needed
        if (null == $zone) {
            $zone = new DateTimeZone('Europe/London');
        }

        // see if $time caller is correct
        try {
            $date = new DateTime($time, $zone);
            $this->date = $date;

            // handle cutoff and processing day
            $this->handleProcessing();

            // handle shipping itself
            $this->handleShipping();
        } catch (Exception $e) {
            $this->error = "Time parsing failed with message:" . PHP_EOL
                           . $e->getMessage() . PHP_EOL;
        }
    }

    public function handleProcessing () {
        // the best way would be to check if yesterday was Sunday or a bank holiday since working days are Mon-Fri
        $processingDate = clone $this->date;

        // get processing day
        if ($this->date->format('w') == 0) {
            $processingDate->modify("+1 day");
        } elseif ($this->date->format('w') == 6) {
            $processingDate->modify("+2 days");
        }

        // check if processing day is a bank holiday
        if ($this->handleHoliday($processingDate)) {
            $processingDate->modify("+1 day");
        }

        // if the processing day is the same as today we can check the cutoff
        // and modify processing to be the following day (again)
        if ($this->date == $processingDate) {
            // we assume the cutoff is a working day
            $cutoff = $this->cutoff["working"];

            // processing the same day means we are in a working day
            // now we need to check the cutoff point offset
            // ie: yesterday was a non-working day
            $yesterday = clone $processingDate;
            $yesterday->modify("-1 day");

            // check if yesterday was Sunday or a bank holiday
            if ($yesterday->format('w') == 0 || $this->handleHoliday($yesterday)) {
                $cutoff = $this->cutoff["non-working"];
            }

            // if we exceeded the cutoff point the processing day is tomorrow
            $cutoffDate = clone $processingDate;
            $cutoffDate->setTime($cutoff, 0);

            if ($processingDate > $cutoffDate) {
                $processingDate->modify("+1 day");
            }
        }

        $this->processingDate = $processingDate;
    }

    public function handleHoliday ($date) {
        foreach ($this->bankHolidays as $holiday) {
            if ($date->format('m-d') == $holiday) {
                return true;
            }
        }

        return false;
    }

    public function handleWorkday ($date) {
        if (in_array($date->format('w'), array(0, 6)) || $this->handleHoliday($date)) {
            return false;
        }

        return true;
    }

    public function handleShipping () {
        $shippingStart = clone $this->processingDate;

        $estimatedShipping = $this->shippingDuration;

        for ($i = 0; $i < $estimatedShipping; $i++) {
            if (! $this->handleWorkday($shippingStart)) {
                $estimatedShipping++;
            }

            $shippingStart->modify("+1 day");
        }

        $this->deliveryDate = $shippingStart->format('Y-m-d') . PHP_EOL;
    }
}
