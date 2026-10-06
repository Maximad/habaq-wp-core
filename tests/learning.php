<?php
/** Isolated behavioral checks; not a replacement for live WordPress staging QA. */
define('ABSPATH', __DIR__);
define('HABAQ_WP_CORE_URL', 'https://example.test/plugin/');
define('HABAQ_WP_CORE_VERSION', '0.3.0');
$GLOBALS['uid'] = 1;
$GLOBALS['caps'] = array('habaq_insider_access');
$GLOBALS['meta'] = array();
$GLOBALS['options'] = array();
$GLOBALS['upload'] = sys_get_temp_dir() . '/habaq-learning-test-' . getmypid();
class Result extends Exception { public $value; public function __construct($value) { $this->value=$value; } }
function get_current_user_id() { return $GLOBALS['uid']; }
function is_user_logged_in() { return $GLOBALS['uid'] > 0; }
function current_user_can($cap) { return in_array($cap, $GLOBALS['caps'], true); }
function get_user_meta($id, $key, $single=true) { return $GLOBALS['meta'][$id][$key] ?? ''; }
function update_user_meta($id,$key,$value) { $GLOBALS['meta'][$id][$key]=$value; }
function delete_user_meta($id,$key) { $exists=isset($GLOBALS['meta'][$id][$key]); unset($GLOBALS['meta'][$id][$key]); return $exists; }
function get_user_by($field,$value) { return $value === 'member@example.test' ? (object)array('ID'=>1) : false; }
function get_userdata($id) { return $id > 0 ? (object)array('ID'=>$id) : false; }
function sanitize_key($s) { return is_string($s) ? preg_replace('/[^a-z0-9_-]/', '', strtolower($s)) : ''; }
function sanitize_title($s) { return sanitize_key($s); }
function sanitize_text_field($s) { return is_scalar($s) ? strip_tags((string)$s) : ''; }
function sanitize_textarea_field($s) { return sanitize_text_field($s); }
function wp_unslash($s) { return $s; }
function esc_html($s) { return htmlspecialchars((string)$s,ENT_QUOTES); }
function esc_attr($s) { return esc_html($s); }
function esc_textarea($s) { return esc_html($s); }
function esc_url($s) { return htmlspecialchars($s,ENT_QUOTES); }
function esc_url_raw($s) { return $s; }
function wp_kses_post($s) { return $s; }
function wp_json_encode($v,$flags=0) { return json_encode($v,$flags); }
function absint($s) { return abs((int)$s); }
function wp_validate_redirect($url,$default) { return str_starts_with($url,'https://example.test/') ? $url : $default; }
function home_url($s='/') { return 'https://example.test'.$s; }
function get_permalink() { return home_url('/learning/'); }
function wp_login_url($url='') { return home_url('/login/'); }
function admin_url($s) { return home_url('/wp-admin/'.$s); }
function wp_nonce_field($action) { echo '<input name="_wpnonce" value="ok">'; }
function check_admin_referer($action) { if (($_POST['_wpnonce']??'')!=='ok') throw new Result('nonce_denied'); }
function check_ajax_referer($action,$key) { if (($_POST[$key]??'')!=='ok') throw new Result('nonce_denied'); }
function wp_die($msg,$title='',$args=array()) { throw new Result(array('die'=>$args['response']??0)); }
function wp_safe_redirect($url) { throw new Result($url); }
function wp_send_json_error($data,$status=400) { throw new Result(array('error'=>$data,'status'=>$status)); }
function wp_send_json_success($data) { throw new Result(array('success'=>$data)); }
function wp_enqueue_style() {}
function selected($a,$b,$echo=false) { return $a===$b ? 'selected' : ''; }
function add_query_arg($key,$value,$url) { return $url.(str_contains($url,'?')?'&':'?').urlencode($key).'='.urlencode($value); }
function get_option($key,$default=array()) { return $GLOBALS['options'][$key]??$default; }
function wp_upload_dir() { return array('basedir'=>$GLOBALS['upload'],'baseurl'=>'https://example.test/uploads'); }
function trailingslashit($s) { return rtrim($s,'/').'/'; }
function __($s,$domain='') { return $s; }
require __DIR__.'/../includes/learning/class-habaq-learning.php';
require __DIR__.'/../includes/training/class-habaq-training-player.php';
$count=0;
function expect($ok,$label) { global $count; if (!$ok) { throw new Exception('FAIL: '.$label); } $count++; }
function submit($data) { $_POST=array_merge(array('_wpnonce'=>'ok','return'=>home_url('/learning/')),$data); try { Habaq_Learning::submit(); } catch(Result $r) { return $r->value; } }
function ajax($method,$data) { $_POST=array_merge(array('nonce'=>'ok','slug'=>'check','version'=>'2','current_slide'=>'1'),$data); try { Habaq_Training_Player::$method(); } catch(Result $r) { return $r->value; } }
$c=Habaq_Learning::catalog();
expect(count($c['modules'])===11,'catalog size');
$source_ids=array_column($c['sources'],'id');
foreach($c['modules'] as $m) { expect(count(array_diff($m['sources'],$source_ids))===0,'valid sources '.$m['id']); expect(Habaq_Learning::grade($m,(string)$m['quiz']['correct']),'correct key '.$m['id']); expect(!Habaq_Learning::grade($m,array(0)),'array answer rejected'); }
$GLOBALS['uid']=0; expect(!Habaq_Learning::eligible(),'anonymous denied'); expect(!str_contains(Habaq_Learning::render(),'من الفكرة'),'anonymous sees no lessons');
$GLOBALS['uid']=1; $GLOBALS['caps']=array('read'); expect(!Habaq_Learning::eligible(),'ordinary login denied');
$GLOBALS['caps']=array('habaq_insider_access');
expect(Habaq_Learning::unlocked(1,'welcome'),'first unlocked');
expect(!Habaq_Learning::unlocked(1,'roles'),'prerequisite enforced');
expect(str_contains(submit(array('op'=>'lesson','lesson'=>'roles','version'=>$c['version'],'ack'=>'1','answer'=>'1')),'invalid'),'cannot skip');
expect(str_contains(submit(array('op'=>'track','track'=>'media')),'saved'),'track save');
expect(count(Habaq_Learning::path(1))===7,'shared plus two lessons');
foreach($c['shared'] as $id) { $m=Habaq_Learning::module($id); expect(str_contains(submit(array('op'=>'lesson','lesson'=>$id,'version'=>$m['version'],'ack'=>'1','answer'=>(string)$m['quiz']['correct'])),'saved'),'complete '.$id); }
expect(str_contains(submit(array('op'=>'lesson','lesson'=>'media-basics','version'=>'old','ack'=>'1','answer'=>'1')),'invalid'),'stale lesson denied');
expect(str_contains(submit(array('op'=>'lesson','lesson'=>'media-basics','version'=>$c['version'],'ack'=>'1','answer'=>'0')),'wrong'),'wrong answer rejected');
expect(str_contains(submit(array('op'=>'lesson','lesson'=>'media-basics','version'=>$c['version'],'answer'=>'1')),'invalid'),'ack required');
submit(array('op'=>'lesson','lesson'=>'media-basics','version'=>$c['version'],'ack'=>'1','answer'=>'1'));
$task=array('op'=>'lesson','lesson'=>'media-task','version'=>$c['version'],'ack'=>'1','answer'=>'1','evidence'=>'A deidentified proposal with a question, sources and a safe review plan.');
expect(str_contains(submit($task),'review'),'practical review pending');
$m=Habaq_Learning::module('media-task'); expect(Habaq_Learning::record(1,$m)['status']==='pending','not auto approved');
expect(str_contains(submit(array('op'=>'review','member'=>'1','lesson'=>'media-task','decision'=>'complete','feedback'=>'Looks complete')),'invalid'),'learner cannot approve');
$GLOBALS['uid']=2;$GLOBALS['caps']=array('manage_options');
expect(str_contains(submit(array('op'=>'review','member'=>'1','lesson'=>'media-task','decision'=>'revise','feedback'=>'Add verification steps')),'saved'),'revision request');
$GLOBALS['uid']=1;$GLOBALS['caps']=array('habaq_insider_access');submit($task);
$GLOBALS['uid']=2;$GLOBALS['caps']=array('manage_options');submit(array('op'=>'review','member'=>'1','lesson'=>'media-task','decision'=>'complete','feedback'=>'Verification steps reviewed'));
expect(Habaq_Learning::record(1,$m)['status']==='complete','admin approval');
expect(Habaq_Learning::record(2,$m)['status']==='new','cross-user isolation');
$GLOBALS['uid']=1;$GLOBALS['caps']=array('habaq_insider_access');
$GLOBALS['meta'][1]['habaq_learning_welcome']['version']='old';
expect(Habaq_Learning::record(1,Habaq_Learning::module('welcome'))['status']==='new','version resets current status');
submit(array('op'=>'lesson','lesson'=>'welcome','version'=>$c['version'],'ack'=>'1','answer'=>'0'));
expect(isset($GLOBALS['meta'][1]['habaq_learning_welcome_history']['old']),'previous version archived');
$_GET=array('lesson'=>'welcome'); $html=Habaq_Learning::render(); expect(!str_contains($html,'"correct"'),'answer keys absent');
expect(!str_contains(submit(array('op'=>'track','track'=>'media','return'=>'https://attacker.test/')),'attacker.test'),'redirect remains local');
expect(submit(array('op'=>'track','track'=>'media','_wpnonce'=>'bad'))==='nonce_denied','CSRF denied');
expect(!empty(Habaq_Learning::export_data('member@example.test')['data']),'privacy export');
expect(Habaq_Learning::erase_data('member@example.test')['items_removed'],'privacy erase');
expect(get_user_meta(1,'habaq_learning_welcome_history',true)==='','history erased');
// Existing player endpoints use canonical metadata, access, version and confirmation.
$root=$GLOBALS['upload'].'/habaq-training/check';mkdir($root,0777,true);
file_put_contents($root.'/training.json',json_encode(array('meta'=>array('access'=>'cap','cap'=>'habaq_core_access','version'=>'2','require_ack'=>true),'slides'=>array(array('id'=>'one'),array('id'=>'two')))));
$GLOBALS['caps']=array('habaq_insider_access');
expect(ajax('ajax_save_progress',array())['status']===403,'player cap enforced');
$GLOBALS['caps']=array('habaq_core_access');
expect(ajax('ajax_save_progress',array('version'=>'old'))['status']===409,'player stale version denied');
expect(ajax('ajax_mark_complete',array('ack'=>'0'))['status']===400,'player ack enforced');
expect(ajax('ajax_mark_complete',array('ack'=>'1','current_slide'=>'0'))['status']===400,'player requires final slide');
expect(ajax('ajax_mark_complete',array('ack'=>'1'))['success']['completed'],'player complete');
$GLOBALS['meta'][1]['habaq_training_progress']['check']['version']='1';
ajax('ajax_save_progress',array());expect(!$GLOBALS['meta'][1]['habaq_training_progress']['check']['completed'],'old completion not carried forward');
expect(ajax('ajax_save_progress',array('slug'=>'unknown'))['status']===400,'unknown player slug denied');
unlink($root.'/training.json');rmdir($root);rmdir(dirname($root));rmdir($GLOBALS['upload']);
echo 'Passed '.$count." behavioral checks.\n";
