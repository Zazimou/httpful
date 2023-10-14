<?php declare(strict_types=1);

namespace Httpful\Exception;


use Exception;

class ConnectionErrorException extends Exception {

	private int|null $curlErrorNumber = null;
	private string|null $curlErrorString = null;


	public function getCurlErrorNumber(): int|null
    {
		return $this->curlErrorNumber;
	}

    /**
     * @param int $curlErrorNumber
     * @return $this
     */
	public function setCurlErrorNumber(int $curlErrorNumber): static
    {
		$this->curlErrorNumber = $curlErrorNumber;

		return $this;
	}

    /**
     * @return string|null
     */
	public function getCurlErrorString(): string|null
    {
		return $this->curlErrorString;
	}

	/**
	 * @param string $curlErrorString
	 * @return $this
	 */
	public function setCurlErrorString(string $curlErrorString): static
    {
		$this->curlErrorString = $curlErrorString;

		return $this;
	}


}