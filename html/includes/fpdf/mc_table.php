<?php
require('fpdf.php');

class PDF_MC_Table extends FPDF
{
var $widths;
var $aligns;

    function Header(){

    global $hoy;
    global $excursion;
    global $hora_salida;
    global $nombre_chofer;
    global $nombre_movil;
    global $nombre_guia;
    global $a;
    global $Gd_outdoors_guia;
    global $desde;
    global $hasta;

    $excursion = str_replace('+', '', $excursion);

        $this->SetFont('Arial','B',20);
        $this->Cell(80);
        $this->Cell(240,10,'HOTEL RIO SERRANO',0,0,'C');
        $this->Ln(5);
        $this->SetFont('Arial','B',14);
        $this->Cell(80);
        $this->Cell(240,10,$excursion,0,0,'C');
        $this->Ln(10);
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'FECHA: '.$desde.' - '.$hasta.' ',0,0,'R');
        $this->Ln(5);
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'HORA DE SALIDA: '.$hora_salida.' hrs ',0,0,'R');
        $this->Cell(80);
        $this->Ln(10);
        $this->Cell(0,10,'CONDUCTOR: '.$nombre_chofer.' ',0,0,'C');
        $this->Ln(5);
        $this->Cell(0,10,'MOVIL: '.$nombre_movil.' ',0,0,'C');
        $this->Ln(5);                                  
        $this->Cell(0,10,'GUIA: '.$Gd_outdoors_guia[$a].' ',0,0,'C');
        $this->Ln(15);

    }

function SetWidths($w)
{
	//Set the array of column widths
	$this->widths=$w;
}

function SetAligns($a)
{
	//Set the array of column alignments
	$this->aligns=$a;
}

function Row($data)
{
	//Calculate the height of the row
	$nb=0;
	for($i=0;$i<count($data);$i++)
		$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
	$h=10*$nb;
	//Issue a page break first if needed
	$this->CheckPageBreak($h);
	//Draw the cells of the row
	for($i=0;$i<count($data);$i++)
	{
		$w=$this->widths[$i];
		$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
		//Save the current position
		$x=$this->GetX();
		$y=$this->GetY();
		//Draw the border
		$this->Rect($x,$y,$w,$h);
		//Print the text
		$this->MultiCell($w,10,$data[$i],0,$a);
		//Put the position to the right of the cell
		$this->SetXY($x+$w,$y);
	}
	//Go to the next line
	$this->Ln($h);
}

function CheckPageBreak($h)
{
	//If the height h would cause an overflow, add a new page immediately
	if($this->GetY()+$h>$this->PageBreakTrigger)
		$this->AddPage($this->CurOrientation);
}

function NbLines($w,$txt)
{
	//Computes the number of lines a MultiCell of width w will take
	$cw=&$this->CurrentFont['cw'];
	if($w==0)
		$w=$this->w-$this->rMargin-$this->x;
	$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	$s=str_replace("\r",'',$txt);
	$nb=strlen($s);
	if($nb>0 and $s[$nb-1]=="\n")
		$nb--;
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$nl=1;
	while($i<$nb)
	{
		$c=$s[$i];
		if($c=="\n")
		{
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$nl++;
			continue;
		}
		if($c==' ')
			$sep=$i;
		$l+=$cw[$c];
		if($l>$wmax)
		{
			if($sep==-1)
			{
				if($i==$j)
					$i++;
			}
			else
				$i=$sep+1;
			$sep=-1;
			$j=$i;
			$l=0;
			$nl++;
		}
		else
			$i++;
	}
	return $nl;
}
}
?>