
              $training = DB::table($this->hr_db . '.employees_trainingprogram')
                   ->where('emp_number',Auth::user()->Employee_id)
                   ->get();
                   $train="";
                   foreach ($training as $key => $value) {
                     $train.='  <tr>
                     <td  height="15px" style="font-size:7pt;" align="center"> '.$value->title_of_seminar.' </td>
                     <td style="font-size:7pt;" align="center"> '.$value->date_from.' </td>
                     <td style="font-size:7pt;" align="center"> '.$value->date_to.' </td>
                     <td style="font-size:7pt;" align="center"> '.$value->no_of_hour.' </td>
                     <td style="font-size:7pt;" align="center"> '.$value->type_of_LD.' </td>
                     <td style="font-size:7pt;" align="center"> '.$value->conducted.' </td>
                 </tr>   ';
                   }
                   if (count($training)<7) {
                    for ($i=count($training); $i < 7; $i++) { 
                      $train.='  <tr>
                      <td height="13px" style="font-size:6pt;" align="center"> </td>
                      <td style="font-size:6pt;" align="center">  </td>
                      <td style="font-size:6pt;" align="center">  </td>
                      <td style="font-size:6pt;" align="center">  </td>
                      <td style="font-size:6pt;" align="center">  </td>         
                  </tr> ';
                    }
                  }






                  $skillsH = DB::table($this->hr_db . '.employees_skillshobbies')
                    ->where('emp_number',Auth::user()->Employee_id)
                    ->get();
                    $skills="";
                   foreach ($skillsH as $key => $value) {
                     $skills.='  <tr>
                     <td rowspan="1" style="font-size:7pt;" align="center"> '.$value->skills_hobbies.' </td>
                     <td rowspan="2" style="font-size:6pt;" align="center"> '.$value->distinction_recognition.' </td>
                     <td style="font-size:7pt;" align="center"> '.$value->membership_org.' </td>   
                 </tr>   ';
                   }
                   if (count($skillsH)<) {
                    for ($i=count($skillsH); $i < 4; $i++) { 
                      $skills.='  <tr>
                      <td height="13px" style="font-size:6pt;" align="center"> </td>
                      <td style="font-size:6pt;" align="center">  </td>
                      <td style="font-size:6pt;" align="center">  </td>
                      <td style="font-size:6pt;" align="center">  </td>
                      <td style="font-size:6pt;" align="center">  </td>         
                  </tr> ';
                    }
                  }




                  <tr>
    <td  width="65%" style="font-size:8pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;" ></td>
        <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" check="true" name="1" value="1">
        YES
        </td>
        <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
        NO
        </td>             
    </tr>
        <tr>
            <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; "> </td>
            <td width="15%" style="font-size:7pt;"> 
            If YES, give details: </td>
            <td width="18%" style="font-size:7pt; border-bottom:1px solid black;"></td>    
        </tr>