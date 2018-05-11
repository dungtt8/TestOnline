<?php
include_once 'dbConnection.php';
session_start();
$email=$_SESSION['email'];
//delete feedback
if(isset($_SESSION['key'])){
if(@$_GET['fdid'] && $_SESSION['key']=='sunny7785068889') {
$id=@$_GET['fdid'];
$result = mysqli_query($con,"DELETE FROM feedback WHERE id='$id' ") or die('Error');
header("location:dash.php?q=3");
}
}

//delete user
if(isset($_SESSION['key'])){
if(@$_GET['demail'] && $_SESSION['key']=='sunny7785068889') {
$demail=@$_GET['demail'];
$r1 = mysqli_query($con,"DELETE FROM rank WHERE email='$demail' ") or die('Error');
$r2 = mysqli_query($con,"DELETE FROM history WHERE email='$demail' ") or die('Error');
$result = mysqli_query($con,"DELETE FROM user WHERE email='$demail' ") or die('Error');
header("location:dash.php?q=1");
}
}
//remove quiz
if(isset($_SESSION['key'])){
if(@$_GET['q']== 'rmquiz' && $_SESSION['key']=='sunny7785068889') {
$eid=@$_GET['eid'];
$result = mysqli_query($con,"SELECT * FROM questions WHERE eid='$eid' ") or die('Error');
while($row = mysqli_fetch_array($result)) {
	$qid = $row['qid'];
$r1 = mysqli_query($con,"DELETE FROM options WHERE qid='$qid'") or die('Error');
$r2 = mysqli_query($con,"DELETE FROM answer WHERE qid='$qid' ") or die('Error');
}
$r3 = mysqli_query($con,"DELETE FROM questions WHERE eid='$eid' ") or die('Error');
$r4 = mysqli_query($con,"DELETE FROM quiz WHERE eid='$eid' ") or die('Error');
$r4 = mysqli_query($con,"DELETE FROM history WHERE eid='$eid' ") or die('Error');

header("location:dash.php?q=5");
}
}
// ham doc file docx
function read_docx($filename){

  $striped_content = '';
  $content = '';

  if(!$filename || !file_exists($filename)) return false;

  $zip = zip_open($filename);
  if (!$zip || is_numeric($zip)) return false;

  while ($zip_entry = zip_read($zip)) {

      if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

      if (zip_entry_name($zip_entry) != "word/document.xml") continue;

      $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

      zip_entry_close($zip_entry);
  }
  zip_close($zip);      
  $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
  $content = str_replace('</w:r></w:p>', "\r\n", $content);
  $striped_content = strip_tags($content);

  return $striped_content;
  
}


//import quiz
if(isset($_SESSION['key'])){
  if(@$_GET['q']== 'importquiz' && $_SESSION['key']=='sunny7785068889') {
    $name = $_POST['name'];
    $name= ucwords(strtolower($name));
    $type= $_POST['format'];
    $sahi = $_POST['right'];
    $wrong = $_POST['wrong'];
    $time = $_POST['time'];
    $tag = $_POST['tag'];
    $desc = $_POST['desc'];
    $id=uniqid();
    $q4=mysqli_query($con,"INSERT INTO quiz VALUES  ('$id','$name' , '$sahi' , '$wrong','2','$time' ,'$desc','$tag', NOW())");
    // xu ly file upload
    
    $format = $_POST['format'];
    
    if ($_FILES['file']['name'] != NULL){
      if(($_FILES['file']['type'] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document") || ($_FILES['file']['type'] == "application/msword")){
        // $tmp_name = $_FILES['file']['tmp_name'];
        $tmp_name = $_FILES['file']['tmp_name'];
        $file_string = read_docx($tmp_name);
        unlink($tmp_name);
      }
      else{
        echo "File upload must be .doc or .docx.";
      }
    }
    else{
      echo "Please choose file.";
    }
    
    // doc file lay du lieu
    // neu la dinh dang GIFT
    
  if($type == "type2"){  
    $j= 0;
    while (strpos($file_string, "}") || strpos($file_string, ".")){
      $j++;
      if ((strpos($file_string,".") != FALSE) && (strpos($file_string,".") > strpos($file_string,"}"))){
        $strposi = strpos($file_string,".");
      }
      elseif ((strpos($file_string,".")==FALSE) || (strpos($file_string,"}") < strpos($file_string,"."))){
        $strposi = strpos($file_string,"}");
      }
      
      $key = $strposi + 1;
      $this_question = substr($file_string,0,$key);
      
      $ans_start = strpos($file_string,"{");
      $ans_end = strpos($file_string,"}");
      $four_ans = substr($this_question, $ans_start + 1, $ans_end - $ans_start - 1);
      $qid = uniqid();
      $choice = 0;
      while (strpos($four_ans, "~") || strpos($four_ans, "=")){
        $oid = uniqid();
        $choice++;
        $start_this_ans = strpos($four_ans, "~") + 1;
        if (((strpos($four_ans, "~") > strpos($four_ans, "=")) && strpos($four_ans, "=")!= FALSE) || strpos($four_ans, "~")==FALSE){
          $start_this_ans = strpos($four_ans, "=") + 1;
          $this_choice = $choice;
          $qans= mysqli_query($con,"INSERT INTO answer VALUES  ('$qid','$oid')") or die('Error61');
        }
        
        $ans_left = substr($four_ans, $start_this_ans + 1);
        
        $i = strlen($four_ans) - strlen($ans_left);
        
        if (strpos($ans_left, "~") || strpos($ans_left, "=")){
          $end_this_ans = strpos($ans_left, "~") + $i - 2;
          
          
          if (((strpos($ans_left, "~") > strpos($ans_left, "=")) && strpos($ans_left, "=")!= FALSE) || strpos($ans_left, "~")==FALSE){
            $end_this_ans = strpos($ans_left, "=") + $i - 2;
            
          }
        }
        else{
          $end_this_ans = strlen($four_ans) - 2;
          if (strpos($ans_left, "\r\n") == FALSE){
            $end_this_ans += 2;
          }
          
          
        }
        
        $one_ans = substr($four_ans, $start_this_ans, $end_this_ans - $start_this_ans);
        
        $four_ans = substr($four_ans, $end_this_ans);
        
        // echo $qid;
        // echo $one_ans;
        // echo $oid;
        $qa=mysqli_query($con,"INSERT INTO options(qid, option, optionid) VALUES  ('$qid','$one_ans','$oid')") or die('Error61');
        
        // echo "oneans ".$one_ans."\n";
        
        
      }  


      $question = substr($this_question, 0, $ans_start);
      if(strlen($this_question) != $ans_end+1){
        $question = $question."______".substr($this_question, $ans_end + 1, strlen($this_question) - $ans_end -1);
      }
      
      
      $q3=mysqli_query($con,"INSERT INTO questions VALUES  ('$id','$qid','$question' , '$this_choice' , '$j','')");
      
      

      $file_string = substr($file_string,$key + 1);
      // echo $file_string;
      
      
    }
  }

    // Neu la dinh dang AIKEN
  if($type == "type1"){
    $j =0;
    while (strpos($file_string, "Câu")){
      $j++;
      $qid = uniqid(); //import
      $number_question = substr($file_string, strpos($file_string,"Câu") + 5, 1);
      $number_question = (int)$number_question; //import
      $start_question = strpos($file_string,"\r\nCâu") + 11;
      $end_question = strpos($file_string,"\r\nANSWER") + 10;
      $this_question = substr($file_string, $start_question, $end_question - $start_question +1);
      // echo $this_question;
      $start_anss = strpos($this_question,"\r\nA");
      $question = substr($this_question, 0, $start_anss); //import
      // echo $question;
      $four_ans = substr($this_question, $start_anss);
      $four_ans = substr($four_ans, 0, strlen($four_ans)-11)."\r\n";
      $right_ans = substr($this_question, -1); //
      
      while (strpos($four_ans, "\n") != FALSE){
        $oid = uniqid();
        $four_ans_withoutenter = substr($four_ans, 2);
        $next_new_line = strpos($four_ans_withoutenter, "\r\n");
        $this_answer = substr($four_ans_withoutenter, 2, $next_new_line - 2);
        $ans = substr($four_ans_withoutenter, 0, 1);
        $len = strlen($four_ans_withoutenter);
        // echo $len;
        if ($len <> 0){
          // echo 2;
          $qa=mysqli_query($con,"INSERT INTO options(qid, option, optionid) VALUES  ('$qid','$this_answer','$oid')") or die('Error61');
          if($ans == $right_ans){
            // echo 1;
            $qans= mysqli_query($con,"INSERT INTO answer VALUES  ('$qid','$oid')") or die('Error61');
          }
        }
        
        $four_ans = substr($four_ans_withoutenter, $next_new_line);
        
      }
      echo $j;
      echo $number_question;
      $q3=mysqli_query($con,"INSERT INTO questions VALUES  ('$id','$qid','$question' , '$j' , '$number_question','')");
      $file_string = substr($file_string, $end_question +1);
      
      
    }
  }
  header("location:dash.php?q=0");
}
}

//add quiz
if(isset($_SESSION['key'])){
if(@$_GET['q']== 'addquiz' && $_SESSION['key']=='sunny7785068889') {
$name = $_POST['name'];
$name= ucwords(strtolower($name));
$total = $_POST['total'];
$sahi = $_POST['right'];
$wrong = $_POST['wrong'];
$time = $_POST['time'];
$tag = $_POST['tag'];
$desc = $_POST['desc'];
$id=uniqid();
$q3=mysqli_query($con,"INSERT INTO quiz VALUES  ('$id','$name' , '$sahi' , '$wrong','$total','$time' ,'$desc','$tag', NOW())");

header("location:dash.php?q=4&step=2&eid=$id&n=$total");
}
}


//add question
if(isset($_SESSION['key'])){
if(@$_GET['q']== 'addqns' && $_SESSION['key']=='sunny7785068889') {
$n=@$_GET['n'];
$eid=@$_GET['eid'];
$ch=@$_GET['ch'];

for($i=1;$i<=$n;$i++)
 {
 $qid=uniqid();
 $qns=$_POST['qns'.$i];
$q3=mysqli_query($con,"INSERT INTO questions VALUES  ('$eid','$qid','$qns' , '$ch' , '$i')");
  $oaid=uniqid();
  $obid=uniqid();
$ocid=uniqid();
$odid=uniqid();
$a=$_POST[$i.'1'];
$b=$_POST[$i.'2'];
$c=$_POST[$i.'3'];
$d=$_POST[$i.'4'];
$qa=mysqli_query($con,"INSERT INTO options VALUES  ('$qid','$a','$oaid')") or die('Error61');
$qb=mysqli_query($con,"INSERT INTO options VALUES  ('$qid','$b','$obid')") or die('Error62');
$qc=mysqli_query($con,"INSERT INTO options VALUES  ('$qid','$c','$ocid')") or die('Error63');
$qd=mysqli_query($con,"INSERT INTO options VALUES  ('$qid','$d','$odid')") or die('Error64');
$e=$_POST['ans'.$i];
switch($e)
{
case 'a':
$ansid=$oaid;
break;
case 'b':
$ansid=$obid;
break;
case 'c':
$ansid=$ocid;
break;
case 'd':
$ansid=$odid;
break;
default:
$ansid=$oaid;
}


$qans=mysqli_query($con,"INSERT INTO answer VALUES  ('$qid','$ansid')");

 }
header("location:dash.php?q=0");
}
}

//quiz start
if(@$_GET['q']== 'quiz' && @$_GET['step']== 2) {
$eid=@$_GET['eid'];
$sn=@$_GET['n'];
$total=@$_GET['t'];
$ans=$_POST['ans'];
$qid=@$_GET['qid'];
$q=mysqli_query($con,"SELECT * FROM answer WHERE qid='$qid' " );
while($row=mysqli_fetch_array($q) )
{
$ansid=$row['ansid'];
}
if($ans == $ansid)
{
$q=mysqli_query($con,"SELECT * FROM quiz WHERE eid='$eid' " );
while($row=mysqli_fetch_array($q) )
{
$sahi=$row['sahi'];
}
if($sn == 1)
{
$q=mysqli_query($con,"INSERT INTO history VALUES('$email','$eid','0','0','0','0',NOW())")or die('Error');
}
$q=mysqli_query($con,"SELECT * FROM history WHERE eid='$eid' AND email='$email' ")or die('Error115');

while($row=mysqli_fetch_array($q) )
{
$s=$row['score'];
$r=$row['sahi'];
}
$r++;
$s=$s+$sahi;
$q=mysqli_query($con,"UPDATE `history` SET `score`=$s,`level`=$sn,`sahi`=$r, date= NOW()  WHERE  email = '$email' AND eid = '$eid'")or die('Error124');

} 
else
{
$q=mysqli_query($con,"SELECT * FROM quiz WHERE eid='$eid' " )or die('Error129');

while($row=mysqli_fetch_array($q) )
{
$wrong=$row['wrong'];
}
if($sn == 1)
{
$q=mysqli_query($con,"INSERT INTO history VALUES('$email','$eid' ,'0','0','0','0',NOW() )")or die('Error137');
}
$q=mysqli_query($con,"SELECT * FROM history WHERE eid='$eid' AND email='$email' " )or die('Error139');
while($row=mysqli_fetch_array($q) )
{
$s=$row['score'];
$w=$row['wrong'];
}
$w++;
$s=$s-$wrong;
$q=mysqli_query($con,"UPDATE `history` SET `score`=$s,`level`=$sn,`wrong`=$w, date=NOW() WHERE  email = '$email' AND eid = '$eid'")or die('Error147');
}
if($sn != $total)
{
$sn++;
header("location:account.php?q=quiz&step=2&eid=$eid&n=$sn&t=$total")or die('Error152');
}
else if( $_SESSION['key']!='sunny7785068889')
{
$q=mysqli_query($con,"SELECT score FROM history WHERE eid='$eid' AND email='$email'" )or die('Error156');
while($row=mysqli_fetch_array($q) )
{
$s=$row['score'];
}
$q=mysqli_query($con,"SELECT * FROM rank WHERE email='$email'" )or die('Error161');
$rowcount=mysqli_num_rows($q);
if($rowcount == 0)
{
$q2=mysqli_query($con,"INSERT INTO rank VALUES('$email','$s',NOW())")or die('Error165');
}
else
{
while($row=mysqli_fetch_array($q) )
{
$sun=$row['score'];
}
$sun=$s+$sun;
$q=mysqli_query($con,"UPDATE `rank` SET `score`=$sun ,time=NOW() WHERE email= '$email'")or die('Error174');
}
header("location:account.php?q=result&eid=$eid");
}
else
{
header("location:account.php?q=result&eid=$eid");
}
}

//restart quiz
if(@$_GET['q']== 'quizre' && @$_GET['step']== 25 ) {
$eid=@$_GET['eid'];
$n=@$_GET['n'];
$t=@$_GET['t'];
$q=mysqli_query($con,"SELECT score FROM history WHERE eid='$eid' AND email='$email'" )or die('Error156');
while($row=mysqli_fetch_array($q) )
{
$s=$row['score'];
}
$q=mysqli_query($con,"DELETE FROM `history` WHERE eid='$eid' AND email='$email' " )or die('Error184');
$q=mysqli_query($con,"SELECT * FROM rank WHERE email='$email'" )or die('Error161');
while($row=mysqli_fetch_array($q) )
{
$sun=$row['score'];
}
$sun=$sun-$s;
$q=mysqli_query($con,"UPDATE `rank` SET `score`=$sun ,time=NOW() WHERE email= '$email'")or die('Error174');
header("location:account.php?q=quiz&step=2&eid=$eid&n=1&t=$t");
}

//add comment

if(@$_GET[q]=='comment'){
  $comment = $_POST['comment'];
  $id_quiz = $_POST['id_quiz'];
  $ins = mysqli_query($con,"INSERT INTO forum (id_quiz, email_user, content) VALUES  ('$id_quiz','$email','$comment')");
  header("location:account.php?q=result&eid=$id_quiz");
}

?>



