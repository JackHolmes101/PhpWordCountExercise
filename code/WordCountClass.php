<?php

class WordCount{

	private $file = ""; // Input file
	private $wordCount = 0; // Total word count
	private $totalLength = 0; // Total combined word length
	private $lengthCountArray = array(); // counts occurence of each word length (wordlength : count)

	private $regexInclude = array("^\\d{1,2}/\\d{2}/\\d{4}^","^\\d{4}/\\d{2}/\\d{1,2}}^",);
	private $regexExclude = array("/\b(\d+)(?:st|nd|rd|th)\b/");
	

	// Applies regex expressions to include and exclude certain words
	private function applyRegex($line){
		// Exlude certain regex from being counted
		foreach ($this->regexExclude as $regex) {
			$line = preg_replace($regex, " ", $line); 
		}
		// Inlude words that are not counted by str_word_count()
		foreach ($this->regexInclude as $regex) {
			preg_match_all($regex, $line, $results);
			foreach ($results[0] as $match) {
				$replacement = str_repeat("x", strlen($match));
				$line = preg_replace($regex, $replacement, $line);
			}
			return $line;	
		}
	}

	// Count the number of words by word length for a given line
	private function countWordLengths($line) {
		foreach ($line as $word) {
			$length = mb_strlen($word);
			$this->totalLength += $length;

			// Record occurences of each word length (word length : count)
			if (!array_key_exists($length, $this->lengthCountArray)) {
				$this->lengthCountArray[$length] = 1;
			}
			else {
				$this->lengthCountArray[$length] += 1;
			}
		}		
	}

	// Read in text file line-by-line and apply processing
	private function processFile() {
		$count = 0;
		$line = "";
		$handle = @fopen($this->file, "r");
		
		if ($handle) {
    		while (($buffer = fgets($handle, 2048)) !== false) {
    			$line = $this->applyRegex($buffer);

				$line = str_word_count($line, 1, '&'); // Get array of words in line
				$this->wordCount += count($line);
				
				$this->countWordLengths($line);
   			}
    		fclose($handle);
    	}
	}

	// Calculate/get stats and output to user
	private function printStats() {
		echo "Word count: ", $this->wordCount, '<br>';

		$averageLength = $this->totalLength/$this->wordCount;
		echo "Average word length = ", number_format($averageLength, 3, '.', ''), "<br>";

		ksort ($this->lengthCountArray);
		foreach ($this->lengthCountArray as $key => $value) {
    		echo "Number of words of length $key is $value <br>";
		}

		$highest = max($this->lengthCountArray);
		$result = array_keys( $this->lengthCountArray, $highest);
		echo "The most frequently occurring word length is $highest, for word lengths of ", implode(' & ', $result), ".";
	}

	public function __construct($pathToFile) {
    	$this->file = $pathToFile;
    	$this->processFile();
		$this->printStats();
   	}


}



?>