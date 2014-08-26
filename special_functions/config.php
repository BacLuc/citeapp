<?php
/*
 * Created on 05.01.2013
 *
 * Copyright by Lucius Bachmann
 * 
 */
 define("_CONFIG_img_path_write", "../img/");
 define("_CONFIG_img_path_db", "src/img/");
 
  define("_CLEANUP_EVERY_CALL", "1");
  define("_DEZIDIERTER_ZUGANG", "1");
  define("_NAME", "SocialTravel");
  define("_EMAIL_FROM", "social@travel.com");
	
  define("_MAX_REISE_PER_MOD", "4");	
   define("MAX_EINLADUNGEN", "4");
   define("MULTIUSER", "0");
  
  
  
   $TABLE_IDFIELD['abstimmung']="abs_id"; 
   $TABLE_IDFIELD['dokumentationseintrag']="doe_id";
   $TABLE_IDFIELD['dokumentationseintrag_enthaelt_medien']="dokumentationseintrag_enthaelt_medien_id";  
   $TABLE_IDFIELD['event']="eve_id";	
   $TABLE_IDFIELD['eventkommentar']="ekm_id";
   $TABLE_IDFIELD['eventkommentar_enthaelt_medien']="ekm_enthaelt_med_id";
   $TABLE_IDFIELD['eventoption']="evo_id";
   $TABLE_IDFIELD['eventoptionskommentar']="eok_id";
   $TABLE_IDFIELD['eventoptionskommentar_enthaelt_medien']="eok_enthaelt_med_id";
   $TABLE_IDFIELD['eventoption_enthaelt_medien']="eventoption_enthaelt_medien_id";
   $TABLE_IDFIELD['event_enthaelt_eventoption']="eee_id";
   $TABLE_IDFIELD['hat_interessen']="hat_interessen_id";
   $TABLE_IDFIELD['interessen']="interessen_id";
   $TABLE_IDFIELD['journal']="jou_id";
   $TABLE_IDFIELD['kontakte']="kontakte_id";
   $TABLE_IDFIELD['location']="loc_id";
   $TABLE_IDFIELD['location_data']="lod_id";
   $TABLE_IDFIELD['medien']="med_id";
   $TABLE_IDFIELD['moderatoren_anfrage']="maf_id";
   $TABLE_IDFIELD['news']="news_id";
   $TABLE_IDFIELD['news_enthaelt_medien']="news_enthaelt_medien_id";
   $TABLE_IDFIELD['reise']="rei_id";
   $TABLE_IDFIELD['reisedokumentation']="red_id";
   $TABLE_IDFIELD['journal']="jou_id";
   $TABLE_IDFIELD['schickt_message']="schickt_message_id";
   $TABLE_IDFIELD['session_log']="sel_id";
   $TABLE_IDFIELD['user']="user_id";
   $TABLE_IDFIELD['user_enthaelt_medien']="user_enthaelt_med_id";
   $TABLE_IDFIELD['user_nimmtteil_abstimmung']="user_nimmtteil_abstimmung_id";
   $TABLE_IDFIELD['user_nimmtteil_reise']="user_nimmtteil_reise_id";
?>
