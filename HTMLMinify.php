<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class HTMLMinify
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($this->validateResponse($response)) {
            // Fetch the original content from the response instance
            $content = $response->getContent();

            // Apply minificaation to the content
            $minified = $this->minify($content);

            // Replace the response content with the new minified version
            $response->setContent($minified);
        }

        return $response;
    }

    /**
     * Validate the response for html content type
     *
     * @param mixed $response
     *
     * @return bool
     */
    protected function validateResponse($response)
    {
        // Check the current response is a valid object and instance of Response
        $responseType = is_object($response) && $response instanceof Response;

        // Only apply the minify to text/html content types
        $type = $response->headers->get('Content-Type');
        $contentType = strtolower(strtok($type, ';')) === 'text/html';

        return ($responseType && $contentType) ? true : false;
    }

    /**
     * Minify the response content
     *
     * Parsing from below link
     * @link http://laravel-tricks.com/tricks/minify-html-output
     *
     * @param $buffer
     * @return mixed
     */
    public function minify($buffer)
    {
        if(strpos($buffer,'<pre>') !== false)
        {
            $replace = array(
                '/<!--[^\[](.*?)[^\]]-->/s' => '',
                "/<\?php/"                  => '<?php ',
                "/\r/"                      => '',
                "/>\n</"                    => '><',
                "/>\s+\n</"    				=> '><',
                "/>\n\s+</"					=> '><',
            );
        }
        else
        {
            $replace = array(
                '/<!--[^\[](.*?)[^\]]-->/s' => '',
                "/<\?php/"                  => '<?php ',
                "/\n([\S])/"                => '$1',
                "/\r/"                      => '',
                "/\n/"                      => '',
                "/\t/"                      => '',
                "/ +/"                      => ' ',
            );
        }
        $buffer = preg_replace(array_keys($replace), array_values($replace), $buffer);
        return $buffer;
    }
}
