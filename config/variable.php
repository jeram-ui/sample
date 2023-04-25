<?php
return [
	'db_lgu' => 'qpsii_lgusystem',
	'db_hr' => 'humanresource',
	'db_trk' => 'documenttracker',
	'db_general' => 'general',
	'db_scheduler' => 'dbfederation',

	'logo' => '<table style="width=100%;">
				<tr>
				<th align="right">
				<img src="' . public_path() . '/images/Logo1.png"  height="60" width="60">
				</th>				
				<th style="font-size:12pt;" align="center"> 
				Republic of the Philippines
				<br>
				Province of Cebu
				<br>
				'.env('cityname', false).'
				<br> 
				</th>			
				<th align="left">
				<img src="' . public_path() . '/images/NAGA LOGO2.png"  height="60" width="65">
				</th>
				</tr>
			</table>',
	'sanitaryLogo' => '<table style="width=100%;">
					<tr>
					<th align="center">
					<img src="' . public_path() . '/images/Logo1.png"  height="60" width="65">
					</th>				
					<th style="font-size:12pt;" align="center"> 
					Republic of the Philippines
					<br>
					Province of Cebu
					<br>
					City Health Office
					<h4>'.env('cityname', false).'</h4>
					<br> 
					</th>				
					<th height="45" width="120" align="right"><h1 style="font-size:45px">' . date("Y") . '</h1></th>
					</tr>					
				</table>',

	'archLogo' => '<table style="width=100%;">
				<tr>
				<th align="right">
				<img src="' . public_path() . '/images/Logo1.png"  height="45" width="45">
				</th>				
				<th style="line-height:8px;" align="center"><h4>Republic of the Philippines</h4>
				<br>
				<h4>Province of Cebu</h4>
				<br>
				<h4>'.env('cityname', false).' </h4>
				<br> 
				</th>			
				<th align="left">
				<img src="' . public_path() . '/images/NAGA LOGO2.png"  height="40" width="45">
				</th>
				</tr>			
			</table>',


	'logo2' => '<table style="width=100%;">
			<tr>
			   <td style="font-size:10pt;" align="center"> 
		          <img src="' . public_path() . '/images/Logo1.png"  height="60" width="60"/>
			  </td>			
			</tr>
			<tr>
			   <td style="font-size:12pt;" align="center"> 
			   Republic of the Philippines
			   <br>
			   Province of Cebu
			   <br>
			   City Mayors Office
			  </td>			
			</tr>
		</table>',

];
