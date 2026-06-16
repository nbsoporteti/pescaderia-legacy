<?php
require('fpdf.php');

class PDF_MC_Table extends FPDF
{
var $widths;
var $aligns;

    function Header(){

	    if ( $this->PageNo() == 1 ) {

	    global $fecha;
	    global $servicio;
	    global $salida_termino_reporte;
	    global $contacto_reporte;
	    global $guia_reporte;
	    global $chofer_reporte;
	    global $movil_reporte;
	    global $cantidad_pasajeros;
	    global $servicio_id;
	    global $a;
	    global $i;
	    global $OCI;

	    $query = 
				"
				SELECT 
	                o.SERVICIO_OBS AS SERVICIO
	            FROM 
	                aabb_platform.G_TB_OUTDOORS o 
	            WHERE o.ID IN($servicio_id)
	            ORDER BY SERVICIO_HABITACION ASC
				";

		$stid2 = oci_parse($OCI, $query);

		$stid_puq_hrs_pnt = oci_parse($OCI, $query);

		oci_execute($stid_puq_hrs_pnt);

		oci_execute($stid2);

		while ($row = oci_fetch_assoc($stid_puq_hrs_pnt)) {

		$servicio_obs = $row['SERVICIO'];

	    if (preg_match('/\+(.+)\+/',$servicio_obs,$coincidencias,PREG_OFFSET_CAPTURE, 0)):
	        $Gd_dato = "";
	        $Gd_dato = str_replace($coincidencias[1][0], "", $servicio_obs);
	        $Gd_dato = str_replace("+","", $Gd_dato);
	        $Gd_dato = str_replace("<","", $Gd_dato);
	        $Gd_dato = str_replace(">","", $Gd_dato);
	        $Gd_dato = str_replace("-","", $Gd_dato);
	    endif;

	    $Gd_dato = str_ireplace($servicio, '', $Gd_dato);
	    $Gd_dato = str_replace('WRAP RES', '', $Gd_dato);
	    $Gd_dato = str_replace('WRAP AVE', '', $Gd_dato);
	    $Gd_dato = str_replace('WRAP SALMON', '', $Gd_dato);
	    $Gd_dato = str_replace('WRAP VEGETARIANO', '', $Gd_dato);
	    $Gd_dato = str_replace('SNACK', '', $Gd_dato);

	    $Gd_dato = trim($Gd_dato);

	    //$Gd_dato = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $Gd_dato);

	    $Gd_dato = preg_replace('#\s+#', ' ', $Gd_dato);
		  
		$cantidad_pasajeros += get_string_between($Gd_dato, '[', ']');

		}

	        $this->SetFont('Arial','B',22);
	        $this->Cell(80);
	        $this->Cell(240,10,'HOTEL RIO SERRANO',0,0,'C');
	        $this->Ln(10);
	        $this->SetFont('Arial','B',18);
	        $this->Cell(80);
	        $this->Cell(240,10,$servicio,0,0,'C');
	        $this->Ln(20);
	        $this->SetFont('Arial','B',16);
	        $this->Cell(80);
	        $this->Cell(240,10,'LISTADO DE PASAJEROS',0,0,'C');
	        $this->Ln(10);
	        $this->SetFont('Arial','B',16);
	        $this->Cell(80);
	        $this->Cell(240,10,'('.$cantidad_pasajeros.')',0,0,'C');
	        $this->Ln(10);
	        $this->SetFont('Arial','B',14);
	        $this->Cell(0,10,'FECHA: '.$fecha.' ',0,0,'R');
	        $this->Ln(5);
	        $this->SetFont('Arial','B',14);
	        $this->Cell(0,10,'HORA DE SALIDA/TERMINO: '.$salida_termino_reporte.' hrs ',0,0,'R');
	        $this->Ln(5);
	        $this->SetFont('Arial','B',14);
	        $this->Cell(0,10,'HORA DE CONTACTO: '.$contacto_reporte.' hrs ',0,0,'R');
	        $this->Cell(80);
	        $this->Ln(10);
	        $this->Cell(0,10,'CONDUCTOR: '.$chofer_reporte.' ',0,0,'C');
	        $this->Ln(5);
	        $this->Cell(0,10,'MOVIL: '.$movil_reporte.' ',0,0,'C');
	        $this->Ln(5);                                  
	        $this->Cell(0,10,'GUIA: '.$guia_reporte.' ',0,0,'C');
	        $this->Ln(20);

	    }

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
		$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';
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