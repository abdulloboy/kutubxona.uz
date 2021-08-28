<?php











namespace Composer\Downloader;




class TransportException extends \RuntimeException
{
protected $headers;
protected $response;

public function setHeaders($headers)
{
$this->headers = $headers;
}

public function getHeaders()
{
return $this->headers;
}

public function setResponse($response)
{
$this->response = $response;
}

public function getResponse()
{
return $this->response;
}
}
