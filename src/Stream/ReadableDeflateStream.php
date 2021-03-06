<?php

/*
 +----------------------------------------------------------------------+
 | PHP Version 7                                                        |
 +----------------------------------------------------------------------+
 | Copyright (c) 1997-2018 The PHP Group                                |
 +----------------------------------------------------------------------+
 | This source file is subject to version 3.01 of the PHP license,      |
 | that is bundled with this package in the file LICENSE, and is        |
 | available through the world-wide-web at the following url:           |
 | http://www.php.net/license/3_01.txt                                  |
 | If you did not receive a copy of the PHP license and are unable to   |
 | obtain it through the world-wide-web, please send a note to          |
 | license@php.net so we can mail you a copy immediately.               |
 +----------------------------------------------------------------------+
 | Authors: Martin Schröder <m.schroeder2007@gmail.com>                 |
 +----------------------------------------------------------------------+
 */

namespace Concurrent\Stream;

class ReadableDeflateStream implements ReadableStream
{
    protected $stream;
    
    protected $context;
    
    protected $buffer = '';
    
    protected $closed;
    
    public function __construct(ReadableStream $stream, int $level = 1, ?int $mode = null)
    {
        $this->stream = $stream;
        $this->context = \deflate_init($mode ?? \ZLIB_ENCODING_GZIP, [
            'level' => $level
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function close(?\Throwable $e = null): void
    {
        if ($this->closed === null) {
            $this->closed = $e ?? true;
            
            $this->stream->close($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read(?int $length = null): ?string
    {
        if ($this->closed) {
            throw new StreamClosedException('Cannot read from closed stream');
        }
        
        if ($this->context === null) {
            return null;
        }
        
        while ($this->buffer === '' && $this->context !== null) {
            $chunk = $this->stream->read();
            
            if ($chunk === null) {
                $this->buffer = \deflate_add($this->context, '', \ZLIB_FINISH);
                $this->context = null;
            } else {
                $this->buffer = \deflate_add($this->context, $chunk, \ZLIB_SYNC_FLUSH);
            }
        }
        
        $chunk = \substr($this->buffer, 0, $length ?? 0xFFFF);
        $this->buffer = \substr($this->buffer, \strlen($chunk));
        
        return $chunk;
    }
}
