<?

class FancySort {
	private $sortFields = null;
	private $isObject = false;

	public function test($array, $field) {
		$arr = $this->sort($array, $field);
	}


	private function arrayCompare($a, $b) {
		for($i=0; $i<count($this->sortFields); $i++) {
			if ($this->isObject) {
				$partsA = $this->parseString($a->{$this->sortFields[$i]});
				$partsB = $this->parseString($b->{$this->sortFields[$i]});
			} else {
				$partsA = $this->parseString($a[$this->sortFields[$i]]);
				$partsB = $this->parseString($b[$this->sortFields[$i]]);
			}

			$cmp = $this->compareParts($partsA, $partsB);

			if ($cmp != 0) return $cmp;
		}

		return 0;
	}


	public function sort($array, $field) {
		$this->isObject = false;

		if (is_array($field)) {
			$this->sortFields = $field;
		} else {
			$this->sortFields = array($field);
		}

		usort($array, array($this, 'arrayCompare'));

		return $array;
	}


	public function sortObjects($array, $property) {
		$this->isObject = true;

		if (is_array($property)) {
			$this->sortFields = $property;
		} else {
			$this->sortFields = array($property);
		}

		usort($array, array($this, 'arrayCompare'));

		return $array;
	}


	private function detectDMT($parts) {
		for($i=0; $i<=count($parts)-5; $i++) {
			if (is_numeric($parts[$i]) && $parts[$i] <= 12 && $parts[$i+1] == "-" && is_numeric($parts[$i+2]) && $parts[$i+2]<=31 && $parts[$i+3] == "-" && is_numeric($parts[$i+4]) && $parts[$i+4] <=2200) {
				// assume it is in m-d-y format.  Put it into y-m-d so it is sortable.
				$y = $parts[$i+4];
				$m = $parts[$i];
				$d = $parts[$i+2];
				/*
				$parts[$i+4] = $parts[$i+2];
				$parts[$i+2] = $parts[$i];
				$parts[$i] = $y;
				
				// invert the numbers to make it sort in reverse order
				$parts[$i] = 10000 - $parts[$i];
				$parts[$i+2] = 10000 - $parts[$i+2];
				$parts[$i+4] = 10000 - $parts[$i+4];
				*/
				
				array_splice($parts, 0, 0, array(10000-$y, 10000-$m, 10000-$d));
				return $parts;
			}
		}
		
		return $parts;
	}

	private function parseString($str) {
		$mode = "letters";	// $mode can be "letters" or "numbers"
		$part = "";	// the stuff we have collected so far
		$parts = array();

		for($i=0; $i<strlen($str); $i++) {
			$char = substr($str, $i, 1);
			$newMode = stristr("1234567890", $char) !== false ? "numbers" : "letters";

			if ($newMode != $mode) {
				// we are changing modes.  So first flush what we have.
				$parts[] = $mode == "letters" ? trim($part) : intval($part);
				$mode = $newMode;
				$part = "";
			}

			$part = $part . $char;
		}
		$parts[] = $mode == "letters" ? trim($part) : intval($part);	// flush the last one
		
		$parts = $this->detectDMT($parts);

		return $parts;
	}

	/*
	Returns 0 if there is no numeric representation for this string.  Otherwise, a number greater than zero.
	*/
	private function strToNumeric($str) {
		$months = array("january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");
		$mons = array("jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec");
		$dows = array("sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday");
		$dow = array("sun", "mon", "tue", "wed", "thu", "fri", "sat");

		if (in_array($str, $months)) return array_search($str, $months) + 1;
		if (in_array($str, $mons)) return array_search($str, $mons) + 1;
		if (in_array($str, $dows)) return array_search($str, $dows) + 1;
		if (in_array($str, $dow)) return array_search($str, $dow) + 1;

		return 0;
	}

	private function compareParts($aParts, $bParts, $index=0) {
		if (count($aParts) <= $index && count($bParts) <= $index) return 0;	// the parts were identical all along
		if (count($aParts) <= $index && count($bParts) > $index) {
			return -1;
		}
		if (count($aParts) > $index && count($bParts) <= $index) {
			return 1;
		}

		$aBit = $aParts[$index];
		$bBit = $bParts[$index];

		if ($aBit == $bBit) {
			return $this->compareParts($aParts, $bParts, $index+1);
		}

		if (!is_numeric($aBit) && !is_numeric($bBit)) {
			$aBit = strtolower($aBit);
			$bBit = strtolower($bBit);

			$aNumeric = $this->strToNumeric($aBit);
			$bNumeric = $this->strToNumeric($bBit);

			if ($aNumeric > 0 && $bNumeric > 0) {
				$aBit = $aNumeric;
				$bBit = $bNumeric;
			}
		}

		if ($aBit < $bBit) {
			return -1;
		}
		if ($aBit > $bBit) {
			return 1;
		}
	}

}
