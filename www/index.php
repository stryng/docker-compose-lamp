<?
ob_start("ob_gzhandler");

require "include/tracker.php";

dbconn(true);
loggedinorreturn();

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
  $choice = $_POST["choice"]+0;
  if ($CURUSER && $choice != "" && $choice < 256 && $choice == floor($choice))
  {
    $res = mysql_query("SELECT * FROM polls ORDER BY added DESC LIMIT 1") or sqlerr();
    $arr = mysql_fetch_assoc($res) or die("No poll");
    $pollid = $arr["id"];
    $userid = $CURUSER["id"];
    $res = mysql_query("SELECT * FROM pollanswers WHERE pollid=$pollid && userid=$userid") or sqlerr();
    $arr = mysql_fetch_assoc($res);
    if ($arr) die("Dupe vote");
    mysql_query("INSERT INTO pollanswers VALUES(0, $pollid, $userid, $choice)") or sqlerr();
    if (mysql_affected_rows() != 1)
      stderr("Error", "An error occured. Your vote has not been counted.");
    header("Location: $BASEURL/");
    die;
  }
  else
    stderr("Error", "Please select an option.");
}

/*
$a = @mysql_fetch_assoc(@mysql_query("SELECT id,username FROM users WHERE status='confirmed' ORDER BY id DESC LIMIT 1")) or die(mysql_error());
if ($CURUSER)
  $latestuser = "<a href=userdetails.php?id=" . $a["id"] . ">" . $a["username"] . "</a>";
else
  $latestuser = $a['username'];
*/

$registered = get_row_count("users");
//$unverified = number_format(get_row_count("users", "WHERE status='pending'"));
$torrents = number_format(get_row_count("torrents"));
//$dead = number_format(get_row_count("torrents", "WHERE visible='no'"));

$r = mysql_query("SELECT value_u FROM avps WHERE arg='seeders'") or sqlerr(__FILE__, __LINE__);
$a = mysql_fetch_row($r);
$seeders = 0 + $a[0];
$r = mysql_query("SELECT value_u FROM avps WHERE arg='leechers'") or sqlerr(__FILE__, __LINE__);
$a = mysql_fetch_row($r);
$leechers = 0 + $a[0];
$peers = $seeders + $leechers;
//$seeders = number_format(get_row_count("peers", "WHERE seeder='yes'"));
//$leechers = number_format(get_row_count("peers", "WHERE seeder='no'"));
//$peers = number_format(get_row_count("peers", ""));
//$uniqpeer = number_format(get_row_count("peers", "WHERE connectable='yes'"));
if ($leechers == 0)
$ratio = 0;
else
$ratio = round($seeders / $leechers * 100);

$dt = gmtime() - 180;
$dt = sqlesc(get_date_time($dt));
/*$result = mysql_query("SELECT SUM(last_access >= $dt) AS totalol FROM users") or sqlerr(__FILE__, __LINE__);

while ($row = mysql_fetch_array ($result))
{
$totalonline          = $row["totalol"];
}*/

/*$dt = gmtime() - 180;
$dt = sqlesc(get_date_time($dt));
$res = mysql_query("SELECT id, username, class, donor, warned FROM users WHERE last_access >= $dt ORDER BY username") or print(mysql_error());
while ($arr = mysql_fetch_assoc($res))

{
if ($activeusers) $activeusers .= ",\n";
switch ($arr["class"])
{
 case UC_SYSOP:
   $arr["username"] = "<font color=#FF0000>" . $arr["username"] . "</font>";
   break;
case UC_ADMINISTRATOR:
   $arr["username"] = "<font color=#FF0000>" . $arr["username"] . "</font>";
   break;
case UC_MODERATOR:
   $arr["username"] = "<font color=#0000FF>" . $arr["username"] . "</font>";
   break;
case UC_UPLOADER:
   $arr["username"] = "<font color=#9900CC>" . $arr["username"] . "</font>";
   break;
case UC_VIP:
   $arr["username"] = "<font color=#FF6600>" . $arr["username"] . "</font>";
   break;
case UC_POWER_USER:
   $arr["username"] = "<font color=#009900>" . $arr["username"] . "</font>";
   break;

}
$donator = $arr["donor"] === "yes";
if ($donator)
 $activeusers .= "<nobr>";
$warned = $arr["warned"] === "yes";
if ($warned)
 $activeusers .= "<nobr>";
if ($CURUSER)
$activeusers .= "<a href=userdetails.php?id={$arr["id"]}><b>{$arr["username"]}</b></a>";
else
$activeusers .= "<b>{$arr["username"]}</b>";
if ($donator)
$activeusers .= "<img src={$pic_base_url}star.gif alt='" .DONOR. " {$$arr["donor"]}'></nobr>";
if ($warned)
$activeusers .= "<img src={$pic_base_url}warned.gif alt='" .WARNED. " {$$arr["warned"]}'></nobr>";
}
if (!$activeusers)
$activeusers = "There have been no active users in the last 15 minutes.";
*/
stdhead("Home");
//echo "<font class=small>Welcome to our newest member, <b>$latestuser</b>!</font>\n";

print("<table width=737 class=main border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>");
print("<h2>" .RECENT_NEWS. "");
if (get_user_class() >= UC_ADMINISTRATOR)
        print(" - <font class=small>[<a class=altlink href=news.php><b>" .NEWS_PAGE. "</b></a>]</font>");
print("</h2>\n");
$res = mysql_query("SELECT * FROM news WHERE ADDDATE(added, INTERVAL 90 DAY) > NOW() ORDER BY added DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) > 0)
{
        print("<table width=100% border=1 cellspacing=0 cellpadding=10><tr><td class=text>\n<ul>");
        while($array = mysql_fetch_array($res))
        {
          print("<li>" . gmdate("Y-m-d",strtotime($array['added'])) . " - " . format_comment($array['body']));
    if (get_user_class() >= UC_ADMINISTRATOR)
    {
            print(" <font size=\"-2\">[<a class=altlink href=news.php?action=edit&newsid=" . $array['id'] . "&returnto=" . urlencode($_SERVER['PHP_SELF']) . "><b>" .EDIT. "</b></a>]</font>");
            print(" <font size=\"-2\">[<a class=altlink href=news.php?action=delete&newsid=" . $array['id'] . "&returnto=" . urlencode($_SERVER['PHP_SELF']) . "><b>" .DELETE_. "</b></a>]</font>");
    }
    print("</li>");
  }
  print("</ul></td></tr></table>\n");
}

 /*if ($CURUSER)
{
  // Get current poll
  $res = mysql_query("SELECT * FROM polls ORDER BY added DESC LIMIT 1") or sqlerr();
  if($pollok=(mysql_num_rows($res)))
  {
          $arr = mysql_fetch_assoc($res);
          $pollid = $arr["id"];
          $userid = $CURUSER["id"];
          $question = format_comment($arr["question"]);
          $o = array($arr["option0"], $arr["option1"], $arr["option2"], $arr["option3"], $arr["option4"],
            $arr["option5"], $arr["option6"], $arr["option7"], $arr["option8"], $arr["option9"],
            $arr["option10"], $arr["option11"], $arr["option12"], $arr["option13"], $arr["option14"],
            $arr["option15"], $arr["option16"], $arr["option17"], $arr["option18"], $arr["option19"]);

  // Check if user has already voted
          $res = mysql_query("SELECT * FROM pollanswers WHERE pollid=$pollid AND userid=$userid") or sqlerr();
          $arr2 = mysql_fetch_assoc($res);
          print("<h2>" .POLL. "");
  }

  

  if (get_user_class() >= UC_MODERATOR)
  {
          print("<font class=small>");
                print(" - [<a class=altlink href=makepoll.php?returnto=main><b>" .P_NEW. "</b></a>]\n");
                if($pollok) {
                  print(" - [<a class=altlink href=makepoll.php?action=edit&pollid=$arr[id]&returnto=main><b>" .EDIT. "</b></a>]\n");
                        print(" - [<a class=altlink href=polls.php?action=delete&pollid=$arr[id]&returnto=main><b>" .DELETE_. "</b></a>]");
                }
                print("</font>");
        }
        print("</h2>\n");
        if($pollok) {
                print("<table width=100% border=1 cellspacing=0 cellpadding=10><tr><td align=center>\n");
          print("<table class=main border=1 cellspacing=0 cellpadding=0><tr><td class=text>");
          print("<p align=center><b>$question</b></p>\n");
          $voted = $arr2;
          if ($voted)
          {
            // display results
            if ($arr["selection"])
              $uservote = $arr["selection"];
            else
              $uservote = -1;
                        // we reserve 255 for blank vote.
            $res = mysql_query("SELECT selection FROM pollanswers WHERE pollid=$pollid AND selection < 20") or sqlerr();

            $tvotes = mysql_num_rows($res);

            $vs = array(); // array of
            $os = array();

            // Count votes
            while ($arr2 = mysql_fetch_row($res))
              $vs[$arr2[0]] += 1;

            reset($o);
            for ($i = 0; $i < count($o); ++$i)
              if ($o[$i])
                $os[$i] = array($vs[$i], $o[$i]);

            function srt($a,$b)
            {
              if ($a[0] > $b[0]) return -1;
              if ($a[0] < $b[0]) return 1;
              return 0;
            }

            // now os is an array like this: array(array(123, "Option 1"), array(45, "Option 2"))
            if ($arr["sort"] == "yes")
                    usort($os, srt);

            print("<table class=main width=100% border=0 cellspacing=0 cellpadding=0>\n");
            $i = 0;
            while ($a = $os[$i])
            {
              if ($i == $uservote)
                $a[1] .= "&nbsp;*";
              if ($tvotes == 0)
                      $p = 0;
              else
                      $p = round($a[0] / $tvotes * 100);
              if ($i % 2)
                $c = "";
              else
                $c = " bgcolor=#ECE9D8";
              print("<tr><td width=1% class=embedded$c><nobr>" . $a[1] . "&nbsp;&nbsp;</nobr></td><td width=99% class=embedded$c>" .
                "<img src=/pic/bar_left.gif><img src=/pic/bar.gif height=9 width=" . ($p * 3) .
                "><img src=/pic/bar_right.gif> $p%</td></tr>\n");
              ++$i;
            }
            print("</table>\n");
                        $tvotes = number_format($tvotes);
            print("<p align=center>" .VOTES. ": $tvotes</p>\n");
          }
          else
          {
            print("<form method=post action=index.php>\n");
            $i = 0;
            while ($a = $o[$i])
            {
              print("<input type=radio name=choice value=$i>$a<br>\n");
              ++$i;
            }
            print("<br>");
            print("<input type=radio name=choice value=255>" .BLANK_VOTE. "<br>\n");
            print("<p align=center><input type=submit value='" .VOTE. "!' class=btn></p>");
          }
?>
</td></tr></table>
<?
if ($voted)
  print("<p align=center><a href=polls.php>" .PREVIOUS_POOLS. "</a></p>\n");
?>
</td></tr></table>

<?
        } else {
                echo "<table width=100% border=1 cellspacing=0 cellpadding=10><tr><td align=center>\n";
          echo "<table class=main border=1 cellspacing=0 cellpadding=0><tr><td class=text>";
          echo"<p align=center><H3>" .NO_ACTIVE_POOLS. "</h3></p>\n";
          echo "</td></tr></table></td></tr></table>";
        }
}*/
?>


<!--table width=100% border=1 cellspacing=0 cellpadding=10><tr>
  <td class=text>
<?=$activeusers?></td>
</tr></table-->

<h2><? print("" .STATS. "")?></h2>
<table width=100% border=1 cellspacing=0 cellpadding=10><tr><td align=center>
<table class=main border=1 cellspacing=0 cellpadding=5>
<tr><td class=rowhead><? print("" .REGISTERED_USERS. "")?></td><td align=right><?=$registered?></td></tr>
<!-- <tr><td class=rowhead>Unconfirmed users</td><td align=right><?=$unverified?></td></tr> -->
<tr><td class=rowhead><? print("" .TORRENTS. "")?></td><td align=right><?=$torrents?></td></tr>
<? if (isset($peers)) { ?>
<tr><td class=rowhead><? print("" .PEERS. "")?></td><td align=right><?=$peers?></td></tr>
<tr><td class=rowhead><? print("" .SEEDERS. "")?></td><td align=right><?=$seeders?></td></tr>
<tr><td class=rowhead><? print("" .LEECHERS. "")?></td><td align=right><?=$leechers?></td></tr>
<tr><td class=rowhead><? print("" .SEEDER_LEECHER_RATIO. "")?></td><td align=right><?=$ratio?></td></tr>
<? } ?>
</table>
</td></tr></table>

<p><font class=small><? print("" .DISCLAIMER. "")?></font></p>

<p align=center>
<a href="http://getfirefox.com"><img border="0" alt="Get Firefox!" title="Get Firefox!" src="pic/firefox_80x15.png"/></a><a href="http://utorrent.com"><img border="0" alt="Get utorrent!" title="Get utorrent!" src="pic/utorrent.png"/></a><a href="http://azureus.sourceforge.net"><img border="0" alt="Get Azureus!" title="Get Azureus!" src="pic/azureus.png"/></a>
</p>
<p>by SCRIPTUX.COM <a href="http://www.scriptux.com" target="_blank">Full Nulled Scripts</a></p>

</td></tr></table>

<?

stdfoot();

?>
