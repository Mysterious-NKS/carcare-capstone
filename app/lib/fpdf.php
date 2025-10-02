<?php
/**
 * SimplePdf — minimal PDF generator (A4, Courier), good enough for tabular rows.
 * Not a full FPDF; intentionally tiny.
 */
class SimplePdf {
    private int $w;
    private int $h;
    private array $pages = [];
    private string $cur = '';
    private float $cursorY = 0;
    private float $lineHeight = 14; // pts
    private string $title = 'Document';

    public function __construct(string $size='A4'){
        // A4 size in points (1/72 in)
        $this->w = 595; $this->h = 842;
        $this->setFont();
    }
    public function setTitle(string $title){ $this->title = $title; }

    private function setFont(){ /* Courier is built-in name in PDF */ }

    public function addPage(){
        if ($this->cur !== '') $this->pages[] = $this->cur;
        $this->cur = '';
        $this->cursorY = $this->h - 72; // top margin 1 inch
        // set font and initial state
        $this->cur .= "BT /F1 10 Tf 72 ".number_format($this->cursorY,2,'.','')." Td ";
    }

    /** write one line; bold just simulated by slight increase in font size */
    public function textLine(string $text, int $size=10, bool $bold=false){
        if ($this->cur === '') $this->addPage();
        // page break
        if ($this->cursorY < 72){ // bottom margin
            $this->pages[] = $this->cur;
            $this->cur = '';
            $this->cursorY = $this->h - 72;
            $this->cur .= "BT /F1 10 Tf 72 ".number_format($this->cursorY,2,'.','')." Td ";
        }
        $fontSize = $bold ? $size+1 : $size;
        $this->cur .= "/F1 {$fontSize} Tf ";
        // escape parentheses
        $s = str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $text);
        $this->cur .= "(". $s .") Tj T* ";
        $this->cursorY -= $this->lineHeight;
    }

    public function outputDownload(string $filename='document.pdf'){
        if ($this->cur !== '') $this->pages[] = $this->cur;

        // Build PDF objects
        $out = "%PDF-1.4\n";
        $ofs = [];

        // 1: Catalog
        $ofs[] = strlen($out);
        $out .= "1 0 obj << /Type /Catalog /Pages 2 0 R /Title (".$this->escape($this->title).") >> endobj\n";

        // 2: Pages
        $kids = '';
        $nPages = count($this->pages);
        for($i=0;$i<$nPages;$i++){ $kids .= (3+$i*2)." 0 R "; }
        $ofs[] = strlen($out);
        $out .= "2 0 obj << /Type /Pages /Kids [ $kids ] /Count $nPages >> endobj\n";

        // 3..N: each Page + its content stream
        for($i=0;$i<$nPages;$i++){
            $contentObj = 4 + $i*2;
            $pageObj    = 3 + $i*2;

            // page object
            $ofs[] = strlen($out);
            $out .= "$pageObj 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 {$this->w} {$this->h}] /Resources << /Font << /F1  ".(2+$nPages*2)." 0 R >> >> /Contents $contentObj 0 R >> endobj\n";

            // content stream
            $stream = "1 0 0 1 0 0 Tm ".$this->pages[$i]." ET";
            $ofs[] = strlen($out);
            $out .= "$contentObj 0 obj << /Length ".strlen($stream)." >> stream\n$stream\nendstream endobj\n";
        }

        // font object (Courier)
        $ofs[] = strlen($out);
        $out .= (3+$nPages*2)." 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Courier >> endobj\n";

        // xref
        $xrefPos = strlen($out);
        $out .= "xref\n0 ".(3+$nPages*2+1)."\n0000000000 65535 f \n";
        foreach($ofs as $o){ $out .= sprintf("%010d 00000 n \n", $o); }
        $out .= "trailer << /Size ".(count($ofs)+1)." /Root 1 0 R >>\nstartxref\n$xrefPos\n%%EOF";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Length: '.strlen($out));
        echo $out;
    }

    private function escape(string $s){
        return str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $s);
    }
}
