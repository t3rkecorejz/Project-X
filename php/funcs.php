<?php
require_once('classes/SteamID.php');

$bitItems = array(
  // sysname, itemname, bitsum, image

  // pistols
  array("INFINITYBLACK", "Infinity Black", "-1", "Infinity_black_icon.png"),
  array("BALROG1", "BALROG-I", "-1", "Balrog1.png"),
  array("JANUS1", "JANUS-1", "-1", "Janus1.png"),

  // heavy
  array("M1887", "Winchester M1887", "-1", "Icon_m1887_cso.png"),
  array("USAS12", "USAS-12 Camo", "-1",  "Usas12camo.png"),
  array("JANUS11", "JANUS-11", "0", "Janus11.png"),
  array("COILGUN", "Coil Gun", "1", "Coilmg.png"),

  // assault rifles
  array("HK416", "HK-416", "-1", "M4a1hk416.png"),
  array("STG44", "StG 44", "-1", "Stg44.png"),
  array("M16A4", "M16A4", "-1", "M16a4.png"),
  array("BLOCKAR", "Brick Piece V2", "1", "Blockar_gfx.png"),
  array("JANUS5", "JANUS-5", "0", "Janus-5.png"),
  array("BALROG5", "BALROG-V", "0", "Balrog5.png"),
  array("PLASMAGUN", "Plasma Gun", "3", "Plasma.png"),

  // sniper rifles
  array("VSK94", "VSK-94 Auto-Sniper", "-1", "Vsk94.png"),
  array("CROSSBOW", "Арбалет", "-1", "Crossbow_gfx.png"),
  array("M95TIGER", "M95 White Tiger", "5",  "Buffm95.png"),

  // equipments
  array("NAPALM", "Граната Напалм", "-1", "Napalm.png"),
  array("FROST", "Граната Заморозка", "-1", "Frost.png"),
  array("HOLYBOMB", "Святая граната", "6", "Holybomb.png"),
  array("CANNONEX", "Red Cannon Dragon", "-1", "Reddragoncannon.png"),
  array("SALAMANDER", "Огнемёт", "-1", "Salamander.png"),
  array("CLAYMORE", "Claymore Mine MDS", "-1", "Claymore.png"),

  // knives
  array("KNIFE", "Seal knife", "-1", "Sealknife.png"),
  array("NEWKATANA", "Катана", "-1", "Katana.png"),
  array("WARHAMMER", "Warhammer Storm Giant", "-1", "Buffmelee.png"),
  array("RUNEBREAKER", "Blade Runebreaker", "-1", "Runebladeenh.png"),
  array("DUALSWORD", "Dual Phantom Slayer", "-1", "Dualswordphantomslayer.png")
);

function addTableLine($line_name, $line_data) {
  echo '<tr>
    <td>'.$line_name.'</td>
    <td>'.$line_data.'</td>
  </tr>';
}

function loadStats($searchId = "id", $searchWord = "") {
  global $sql_connection;
  $searchWord = addslashes(stripslashes($searchWord));

  $sql_query = $sql_connection->prepare("
    SELECT id, steamid, name, zclass, value1, value2, bitsum, slot1, slot2, slot3, slot4, last_online, online_time, damage, death, infect, infected, kill_zombies, kill_humans, kill_nems, kill_survs, be_nem, be_surv
    FROM zp_save_data
    WHERE (steamid LIKE '%$searchWord%')
    AND id = $searchId;");
  $sql_query->bind_result($id, $steamid, $name, $zclass, $value1, $value2, $bitsum, $primary, $secondary, $melee, $equipment, $last_online, $online_time, $damage, $death, $infect, $infected, $kill_zombies, $kill_humans, $kill_nems, $kill_survs, $be_nem, $be_surv);
  $sql_query->execute();

  $count = array();
  while ($sql_query->fetch()) {
    $player = new stdClass();

    $player->id = $id;
    $player->steamid = $steamid;
    $player->name = $name;
    $player->zclass = $zclass;
    $player->value1 = $value1;
    $player->value2 = $value2;
    $player->bitsum = $bitsum;
    $player->primary = $primary;
    $player->pistol = $secondary;
    $player->knife = $melee;
    $player->equip = $equipment;
    $player->online = $last_online;
    $player->time = $online_time;
    $player->damage = $damage;
    $player->death = $death;
    $player->infect = $infect;
    $player->infected = $infected;
    $player->kill_zombies = $kill_zombies;
    $player->kill_humans = $kill_humans;
    $player->kill_nems = $kill_nems;
    $player->kill_survs = $kill_survs;
    $player->be_nem = $be_nem;
    $player->be_surv = $be_surv;

    array_push($count, $player);
  }

  return $count;
}

function loadAchiev($searchId = "id") {
  global $sql_connection;

  $sql_query = $sql_connection->prepare("
    SELECT id, userid, achiev_id, achiev_value, achiev_date
    FROM zp_achievements
    WHERE userid = $searchId;");
  $sql_query->bind_result($id, $userid, $achiev_id, $achiev_value, $achiev_date);
  $sql_query->execute();

  $count = array();
  while ($sql_query->fetch()) {
    $achiev = new stdClass();

    $achiev->id = $id;
    $achiev->userid = $userid;
    $achiev->achiev_id = $achiev_id;
    $achiev->value = $achiev_value;
    $achiev->date = $achiev_date;

    array_push($count, $achiev);
  }

  return $count;
}

function getWeaponData($slot_data) {
  global $bitItems;
  $data_number = -1;
  for($i = 0; $i < count($bitItems); $i++) {
    if($slot_data == $bitItems[$i][0]) {
      $data_number = $i;
      break;
    } else continue;
  }

  if($data_number == -1) $table_line_data = $slot_data;
  else
    $table_line_data = '<img src="images/items/'.$bitItems[$data_number][3].'" alt="wpn" style="height: 22px;" /> '.$bitItems[$data_number][1];

  return $table_line_data;
}

function getEfficiency($kills, $dies) {
  if($dies == 0) $dies = 1;
  $result = round(((100 * $kills) / ($kills + $dies)), 2);

  return $result;
}

function addProgressToRow($header, $percent) {
  echo "
  <tr>
    <td scope='row'>$header</td>
    <td class='align-middle'>";
      createProgressBar($percent);
    echo "
    </td>
  </tr>";
}

function createProgressBar($percent) {
  $pb_color = "";
  if(0 <= $percent && $percent < 25) { $pb_color = ""; }
  else if(25 <= $percent && $percent < 50) { $pb_color = "bg-success"; }
  else if(50 <= $percent && $percent < 75) { $pb_color = "bg-warning"; }
  else if(75 <= $percent && $percent < 100) { $pb_color = "bg-danger"; }

  echo "
  <div class='progress'>
    <div class='progress-bar progress-bar-striped progress-bar-animated $pb_color' role='progressbar' style='width: $percent%;'
      aria-valuenow='$percent' aria-valuemin='0' aria-valuemax='100'>$percent%</div>
  </div>";
}

function drawAchievement($achivId, $achivTitle, $achivText, $achivDate, $achivImage, $achivValue, $achivMax) {
  $achiv_unlocked = false;
  $progress = (int)(($achivValue / $achivMax) * 100);
  if($achivValue < $achivMax) {
    $class_locked = "achievement-locked";
    $achiv_unlocked = false;
  } else $achiv_unlocked = true;
  ?>
  <div class="achievement <?=$class_locked?>">
    <div class="media">
      <?php if($achivImage != null) { ?>
      <img src="<?=$achivImage?>" alt="achievement-image" style="width: 64px; height: 64px;">
      <?php } ?>
      <div class="media-body">
        <div class="row">
          <div class="col">
            <h5><?=$achivTitle?></h5>
          </div>
          <?php if($achiv_unlocked == true) { ?>
          <div class="col text-right" style="padding-top: 4px;">
            <span style="font-size: 24px;"><i><?=$achivDate?></i></span>
          </div>
          <?php } ?>
        </div>
        <span><?=$achivText?></span>
      </div>
    </div>
    <?php
    $achivValue = number_format($achivValue, 0, '', ' ');
    $achivMax = number_format($achivMax, 0, '', ' ');
    if($achivValue <= 0) { ?>
      <div class="progress" style="margin: 10px 0 10px 0;">
        <div class="progress-bar bg-secondary" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">0 / <?=$achivMax?></div>
      </div>
    <?php } else { ?>
      <div class="progress" style="margin: 10px 0 10px 0;">
        <?php if($progress >= 100) { ?>
          <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?=$progress?>%;" aria-valuenow="<?=$progress?>" aria-valuemin="0" aria-valuemax="100">Выполнено [<?=$achivMax?> / <?=$achivMax?>]</div>
        <?php } else if($progress >= 50) { ?>
          <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?=$progress?>%;" aria-valuenow="<?=$progress?>" aria-valuemin="0" aria-valuemax="100"><?=$achivValue?> / <?=$achivMax?></div>
        <?php } else { ?>
          <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?=$progress?>%;" aria-valuenow="<?=$progress?>" aria-valuemin="0" aria-valuemax="100"></div>
          <span class="pl-2"><?=$achivValue?> / <?=$achivMax?></span>
        <?php } ?>
      </div>
    <?php } ?>
  </div>
  <?php
}
