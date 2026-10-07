<?php
/** Isolated behavioral checks; not a replacement for live WordPress staging QA. */
define('ABSPATH', __DIR__);
define('HABAQ_WP_CORE_URL', 'https://example.test/plugin/');
define('HABAQ_WP_CORE_VERSION', '0.5.0');
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
function get_userdata($id) { return $id > 0 ? (object)array('ID'=>$id,'display_name'=>'Member '.$id) : false; }
function user_can($id,$cap) { return $id===1 ? $cap==='habaq_insider_access' : ($id===2 && $cap==='manage_options'); }
function update_option($key,$value,$autoload=false) { $GLOBALS['options'][$key]=$value; return true; }
class WP_User_Query { public function __construct($args) {} public function get_results() { return array(get_userdata(1)); } public function get_total() { return 1; } }
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
require __DIR__.'/../includes/learning/class-habaq-learning-journey.php';
require __DIR__.'/../includes/learning/class-habaq-learning-library.php';
require __DIR__.'/../includes/training/class-habaq-training-player.php';
$count=0;
function expect($ok,$label) { global $count; if (!$ok) { throw new Exception('FAIL: '.$label); } $count++; }
function submit($data) { $_POST=array_merge(array('_wpnonce'=>'ok','return'=>home_url('/learning/')),$data); try { Habaq_Learning::submit(); } catch(Result $r) { return $r->value; } }
function review($decision,$extra=array()) { $m=Habaq_Learning::module('media-task'); $record=Habaq_Learning::record(1,$m); return submit(array_merge(array('op'=>'review','member'=>'1','lesson'=>'media-task','decision'=>$decision,'feedback'=>'Specific review feedback','version'=>$m['version'],'revision'=>(string)($record['revision']??0),'criteria'=>array_keys(Habaq_Learning_Journey::criteria())),$extra)); }
function ajax($method,$data) { $_POST=array_merge(array('nonce'=>'ok','slug'=>'check','version'=>'2','current_slide'=>'1'),$data); try { Habaq_Training_Player::$method(); } catch(Result $r) { return $r->value; } }
$c=Habaq_Learning::catalog();
expect(count($c['modules'])===61,'catalog size');
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
expect(str_contains(review('revise'),'saved'),'revision request');
$GLOBALS['uid']=1;$GLOBALS['caps']=array('habaq_insider_access');submit($task);
$GLOBALS['uid']=2;$GLOBALS['caps']=array('manage_options');review('complete');
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
// Onboarding operations and proportional authority.
expect(Habaq_Learning_Journey::valid_date('2026-10-07'),'start date valid');
expect(!Habaq_Learning_Journey::valid_date('2026-02-30'),'invalid calendar day denied');
expect(!Habaq_Learning_Journey::valid_date(array()),'array date denied');
expect(Habaq_Learning_Journey::due('2026-10-07',42)==='2026-11-18','followup date');
expect(str_contains(submit(array('op'=>'plan','member'=>'1','revision'=>'0','role'=>'Reporter')),'invalid'),'learner cannot create plan');
$GLOBALS['meta'][1]['habaq_learning_media-task']['status']='pending';
expect(str_contains(submit(array('op'=>'reflection','applied'=>'test','obstacle'=>'test','next_goal'=>'test')),'invalid'),'followup requires reviewed practical task');
$GLOBALS['meta'][1]['habaq_learning_media-task']['status']='complete';
// Restore the stale foundation record through the actual learner endpoint.
$m=Habaq_Learning::module('welcome');submit(array('op'=>'lesson','lesson'=>'welcome','version'=>$m['version'],'ack'=>'1','answer'=>(string)$m['quiz']['correct']));
$plan=array('op'=>'plan','member'=>'1','revision'=>'0','role'=>'Reporter','mentor'=>'Learning companion','first_task'=>'A small safe proposal with a reviewer and deadline','start_date'=>'2026-10-07','weekly_minutes'=>'60','kickoff'=>'1');
$GLOBALS['uid']=2;$GLOBALS['caps']=array('manage_options');
expect(str_contains(submit($plan),'saved'),'admin creates plan');
expect(str_contains(submit($plan),'stale'),'stale plan cannot overwrite');
expect(str_contains(submit(array_merge($plan,array('member'=>'3'))),'invalid'),'ordinary user cannot receive member plan');
expect(str_contains(submit(array_merge($plan,array('revision'=>'1','start_date'=>'2026-02-30'))),'invalid'),'bad start date denied at endpoint');
expect(str_contains(submit(array_merge($plan,array('revision'=>'1','weekly_minutes'=>'9999'))),'invalid'),'unrealistic workload denied');
expect(str_contains(submit(array_merge($plan,array('revision'=>array('1')))),'stale'),'malformed revision denied');
expect(str_contains(submit(array('op'=>'learning_settings','support_contact'=>'Team channel','report_contact'=>'Safety contact','alternate_contact'=>'Alternative contact')),'saved'),'save contact settings');
$GLOBALS['uid']=1;$GLOBALS['caps']=array('habaq_insider_access');
expect(str_contains(submit(array('op'=>'support','category'=>'tools','note'=>'Need access to the work folder')),'saved'),'member support request');
expect(str_contains(submit(array('op'=>'support','category'=>'unknown','note'=>'test')),'invalid'),'invalid blocker rejected');
expect(str_contains(submit(array('op'=>'support','category'=>'tools','note'=>str_repeat('م',601))),'invalid'),'Arabic character limit enforced');
expect(str_contains(submit(array('op'=>'support','category'=>'tools','note'=>array('test'))),'invalid'),'malformed note rejected');
expect(str_contains(submit(array('op'=>'support_reply','member'=>'1','revision'=>'1','feedback'=>'test')),'invalid'),'learner cannot close support');
$GLOBALS['uid']=2;$GLOBALS['caps']=array('manage_options');
expect(str_contains(submit(array('op'=>'support_reply','member'=>'1','revision'=>'0','feedback'=>'Access requested')),'stale'),'stale support response blocked');
expect(str_contains(submit(array('op'=>'support_reply','member'=>'1','revision'=>'1','feedback'=>'Folder access arranged with the owner')),'saved'),'admin support reply');
$GLOBALS['uid']=1;$GLOBALS['caps']=array('habaq_insider_access');
$reflection=array('op'=>'reflection','applied'=>'Used the task card for a safe hypothetical proposal','obstacle'=>'Need a source verification example','next_goal'=>'Practice one claim/source matrix');
expect(str_contains(submit($reflection),'review'),'submit application reflection');
expect(str_contains(submit($reflection),'invalid'),'pending reflection immutable');
$GLOBALS['uid']=2;$GLOBALS['caps']=array('manage_options');
expect(str_contains(submit(array('op'=>'reflection_review','member'=>'1','revision'=>'0','decision'=>'complete','feedback'=>'test')),'stale'),'stale reflection review rejected');
expect(str_contains(submit(array('op'=>'reflection_review','member'=>'1','revision'=>'1','decision'=>'revise','feedback'=>'Clarify the next small step')),'saved'),'request reflection clarification');
$GLOBALS['uid']=1;$GLOBALS['caps']=array('habaq_insider_access');
expect(str_contains(submit($reflection),'review'),'resubmit reflection');
$GLOBALS['caps']=array('manage_options');
expect(str_contains(submit(array('op'=>'reflection_review','member'=>'1','revision'=>'3','decision'=>'complete','feedback'=>'test')),'invalid'),'no self approval of reflection');
$GLOBALS['uid']=2;
expect(str_contains(submit(array('op'=>'reflection_review','member'=>'1','revision'=>'3','decision'=>'complete','feedback'=>'Discussed application and next goal')),'saved'),'complete reflection review');
expect(Habaq_Learning_Journey::completed(1),'journey complete only with all milestones');
// Practical approval enforces published criteria, current version and exact submission revision.
$GLOBALS['meta'][1]['habaq_learning_media-task']['status']='pending';
expect(str_contains(review('complete',array('criteria'=>array('purpose','quality','handover'))),'invalid'),'safety criterion cannot be omitted');
expect(str_contains(review('complete',array('criteria'=>array('purpose','purpose','purpose','purpose'))),'invalid'),'duplicate criteria not accepted');
expect(str_contains(review('complete',array('version'=>'old')),'invalid'),'stale catalog review rejected');
expect(str_contains(review('complete',array('revision'=>'0')),'invalid'),'stale submission review rejected');
$GLOBALS['uid']=1;
expect(str_contains(review('complete'),'invalid'),'no self approval of task');
$GLOBALS['uid']=2;
expect(str_contains(review('complete'),'saved'),'current submission with four criteria approved');
$GLOBALS['uid']=1;$GLOBALS['caps']=array('habaq_insider_access');
expect(str_contains(submit(array('op'=>'track','track'=>'people')),'saved'),'switch unit');
expect(!Habaq_Learning_Journey::completed(1),'track change does not carry whole journey completion');
expect(!empty(Habaq_Learning_Journey::meta(1,'reflection_history')),'track reflection archived');
expect(Habaq_Learning_Journey::foundation_done(1),'track switch preserves foundation');
$_GET=array('lesson'=>'people-task');$html=Habaq_Learning::render();
expect(str_contains($html,'خطة جلسة تجريبية'),'locked lesson readable');
expect(!str_contains($html,'name="op" value="lesson"'),'locked completion form absent');
$_GET=array();$html=Habaq_Learning::render();
expect(str_contains($html,'ناس: لقاء ثقافي يتيح المشاركة'),'next incomplete lesson opens automatically');
expect(!str_contains($html,'"correct"'),'expanded dashboard still hides keys');
expect(str_contains($html,'اتفاق البداية الخاص بي'),'member can see plan');
expect(str_contains($html,'Folder access arranged'),'member can see support reply');
$GLOBALS['uid']=2;$GLOBALS['caps']=array('manage_options');$_GET=array('member'=>'1');ob_start();Habaq_Learning::admin();$admin_html=ob_get_clean();
expect(str_contains($admin_html,'حفظ اتفاق البداية'),'admin plan form renders');
expect(str_contains($admin_html,'name="revision"'),'admin form includes concurrency guard');
$_GET=array('member'=>'3');ob_start();Habaq_Learning::admin();$admin_html=ob_get_clean();
expect(str_contains($admin_html,'لا يملك وصول عضو حبق'),'unverified-user plan blocked in UI');
$_GET=array();ob_start();Habaq_Learning::admin();$admin_html=ob_get_clean();
expect(str_contains($admin_html,'فتح الخطة والمراجعات'),'manager member queue renders');
$GLOBALS['uid']=1;$GLOBALS['caps']=array('habaq_insider_access');ob_start();Habaq_Learning::admin();$admin_html=ob_get_clean();
expect($admin_html==='','learner sees no admin page');
expect(!empty(Habaq_Learning::export_data('member@example.test')['data']),'privacy export');
expect(Habaq_Learning::erase_data('member@example.test')['items_removed'],'privacy erase');
expect(get_user_meta(1,'habaq_learning_welcome_history',true)==='','history erased');
foreach(Habaq_Learning_Journey::META_KEYS as $key) { expect(get_user_meta(1,$key,true)==='', 'journey data erased '.$key); }
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
// Specialist learning remains independent of induction and system permissions.
$GLOBALS['uid']=1;$GLOBALS['caps']=array('habaq_insider_access');$GLOBALS['meta'][1]=array();
$optional=Habaq_Learning::module('radio-rights');
expect(count(Habaq_Learning_Library::courses())===36,'specialist course count');
expect(count($c['roles'])===28,'role profile count');
expect(count($c['tracks'])===10,'ten induction choices');
expect(count(array_unique(array_column($c['modules'],'id')))===61,'stable unique module IDs');
foreach($c['roles'] as $role=>$profile) {
    expect(isset($c['tracks'][$profile['track']]),'role induction track '.$role);
    foreach(array_merge($profile['priority'],$profile['development']) as $id) { expect(!empty(Habaq_Learning::module($id)['optional']),'role course exists '.$role.' '.$id); }
}
expect(Habaq_Learning::unlocked(1,'radio-rights'),'specialist check available before induction');
expect(!Habaq_Learning::unlocked(1,'imaginary-course'),'unknown specialist denied');
expect(count(Habaq_Learning::path(1))===5,'library does not expand required induction');
expect(str_contains(submit(array('op'=>'lesson','lesson'=>'radio-rights','version'=>$optional['version'],'ack'=>'1','answer'=>'0')),'wrong'),'wrong specialist answer not recorded');
expect(Habaq_Learning::record(1,$optional)['status']==='new','wrong specialist remains new');
expect(str_contains(submit(array('op'=>'lesson','lesson'=>'radio-rights','version'=>'old','ack'=>'1','answer'=>'1')),'invalid'),'stale specialist blocked');
expect(str_contains(submit(array('op'=>'lesson','lesson'=>'radio-rights','version'=>$optional['version'],'ack'=>'1','answer'=>'1')),'saved'),'specialist understanding saved');
expect(Habaq_Learning::record(1,$optional)['status']==='complete','specialist self check complete');
expect(!Habaq_Learning_Journey::foundation_done(1),'optional completion does not finish foundation');
expect(!Habaq_Learning_Journey::completed(1),'optional completion does not grant onboarding approval');
expect(Habaq_Learning::record(2,$optional)['status']==='new','specialist user isolation');
expect($GLOBALS['caps']===array('habaq_insider_access'),'no capability changes');
$role_courses=array_column(Habaq_Learning_Library::courses('photographer'),'id');
expect(in_array('photo',$role_courses,true)&&in_array('safeguarding',$role_courses,true)&&!in_array('reconciliation',$role_courses,true),'photographer recommendation filter');
expect(count(Habaq_Learning_Library::courses('','ميزانية'))>0,'Arabic search');
expect(count(Habaq_Learning_Library::courses('','no_matching_course_987'))===0,'empty search result');
$_GET=array('view'=>'library','role'=>'photographer','q'=>'');$markup=Habaq_Learning::render();
expect(str_contains($markup,'تصوير فوتوغرافي')&&str_contains($markup,'أولوية للمهمة'),'role library rendered');
expect(get_user_meta(1,'habaq_learning_track',true)==='','role filter does not assign a track');
$_GET=array('view'=>'library','lesson'=>'radio-rights');$markup=Habaq_Learning::render();
expect(str_contains($markup,'تم حفظ التحقق')&&!str_contains($markup,'name="answer"'),'completed specialist read without another submission');
$_GET=array('view'=>'library','q'=>'<img src=x onerror=alert(1)>');$markup=Habaq_Learning::render();
expect(!str_contains($markup,'<img src=x'),'search input escaped');
$_GET=array('view'=>'library','role'=>array('bad'),'q'=>array('bad'),'lesson'=>array('bad'));expect(str_contains(Habaq_Learning::render(),'كل الأدوار'),'array filters safely ignored');
$GLOBALS['caps']=array('read');expect(!str_contains(Habaq_Learning::render(),'بث خطي'),'ordinary login cannot read specialist content');
$GLOBALS['caps']=array('habaq_insider_access');$_GET=array();
$export=Habaq_Learning::export_data('member@example.test');expect(str_contains(json_encode($export),'habaq_learning_radio-rights'),'specialist privacy export');
Habaq_Learning::erase_data('member@example.test');expect(get_user_meta(1,'habaq_learning_radio-rights',true)==='','specialist privacy erase');
// Every new functional induction follows the same protected practical workflow.
foreach(array('radio','operations','finance','people-ops','research','technology','leadership') as $track) {
    $GLOBALS['uid']=1;$GLOBALS['caps']=array('habaq_insider_access');$GLOBALS['meta'][1]=array();
    submit(array('op'=>'track','track'=>$track));
    expect(count(Habaq_Learning::path(1))===7,'bounded induction '.$track);
    $ids=$c['tracks'][$track]['modules'];$task=Habaq_Learning::module($ids[1]);
    expect(str_contains(submit(array('op'=>'lesson','lesson'=>$task['id'],'version'=>$task['version'],'ack'=>'1','answer'=>'2','evidence'=>'Deidentified task summary with safe scope and reviewer.')),'invalid'),'new track prerequisites '.$track);
    foreach(array_merge($c['shared'],array($ids[0])) as $id) {$m=Habaq_Learning::module($id);submit(array('op'=>'lesson','lesson'=>$id,'version'=>$m['version'],'ack'=>'1','answer'=>(string)$m['quiz']['correct']));}
    submit(array('op'=>'lesson','lesson'=>$task['id'],'version'=>$task['version'],'ack'=>'1','answer'=>'2','evidence'=>'Deidentified task summary with safe scope and reviewer.'));
    $record=Habaq_Learning::record(1,$task);expect($record['status']==='pending','human practical review '.$track);
    expect(!Habaq_Learning_Journey::task_done(1),'pending not passed '.$track);
    $GLOBALS['uid']=2;$GLOBALS['caps']=array('manage_options');
    submit(array('op'=>'review','member'=>'1','lesson'=>$task['id'],'decision'=>'complete','feedback'=>'Good safe purpose, quality and handover.','version'=>$task['version'],'revision'=>(string)$record['revision'],'criteria'=>array_keys(Habaq_Learning_Journey::criteria())));
    expect(Habaq_Learning_Journey::task_done(1),'new functional practical approved '.$track);
}
echo 'Passed '.$count." behavioral checks.\n";
