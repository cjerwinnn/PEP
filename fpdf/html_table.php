<?php
require_once('fpdf.php'); // ✅ ensures it's only loaded once

class PDF_HTML extends FPDF
{
    protected $B = 0;
    protected $I = 0;
    protected $U = 0;
    protected $HREF = '';

    function WriteHTML($html)
    {
        $html = str_replace("\n", ' ', $html); // remove line breaks
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $tag = false;

        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                // Text content
                if ($this->HREF) {
                    $this->PutLink($this->HREF, $e); // Fixed here
                } else {
                    $this->Write(5, html_entity_decode($e));
                }
            } else {
                // HTML tag
                if ($e[0] == '/') {
                    $this->CloseTag(strtoupper(substr($e, 1)));
                } else {
                    // Opening tag with attributes
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = [];
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3)) {
                            $attr[strtoupper($a3[1])] = $a3[2];
                        }
                    }
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }

    function OpenTag($tag, $attr)
    {
        if ($tag == 'B' || $tag == 'I' || $tag == 'U') {
            $this->SetStyle($tag, true);
        }

        if ($tag == 'A') {
            $this->HREF = $attr['HREF'] ?? '';
        }

        if ($tag == 'BR') {
            $this->Ln(5);
        }
    }

    function CloseTag($tag)
    {
        if ($tag == 'B' || $tag == 'I' || $tag == 'U') {
            $this->SetStyle($tag, false);
        }

        if ($tag == 'A') {
            $this->HREF = '';
        }
    }

    function SetStyle($tag, $enable)
    {
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach (['B', 'I', 'U'] as $s) {
            if ($this->$s > 0) {
                $style .= $s;
            }
        }
        $this->SetFont('', $style);
    }

    function PutLink($URL, $txt)
    {
        $this->SetTextColor(0, 0, 255);
        $this->SetStyle('U', true);
        $this->Write(5, $txt, $URL);
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }
}
?>
