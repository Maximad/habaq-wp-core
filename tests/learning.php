<?php
/** Isolated behavioral checks; not a replacement for live WordPress staging QA. */
define('ABSPATH', __DIR__);
define('HABAQ_WP_CORE_URL', 'https://example.test/plugin/');
define('HABAQ_WP_CORE_VERSION', '0.16.0');
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
function apply_filters($name,$value) { return $value; }
function wp_get_attachment_image($id,$size,$icon,$attributes) { $GLOBALS['photo_requests']=($GLOBALS['photo_requests']??0)+1; return '<img class="habaq-learning__photo" src="https://example.test/habaq-photo.jpg" alt="Habaq photo">'; }
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
expect(str_contains($html,'مهمتك الأولى: خطة جلسة'),'locked lesson readable');
expect(!str_contains($html,'name="op" value="lesson"'),'locked completion form absent');
$_GET=array();$html=Habaq_Learning::render();
expect(str_contains($html,'بداية العمل في حبق ناس'),'next incomplete lesson opens automatically');
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
// Reading mode is presentation-only and survives normal navigation and submissions.
$GLOBALS['uid']=1;$GLOBALS['caps']=array('habaq_insider_access');
$before=$GLOBALS['meta'];$GLOBALS['photo_requests']=0;
$_GET=array('reading'=>'text','lesson'=>'welcome');$markup=Habaq_Learning::render();
expect($GLOBALS['photo_requests']===0&&!str_contains($markup,'habaq-photo.jpg'),'text mode omits image generation and markup');
expect(str_contains($markup,'reading=text&amp;lesson=roles#habaq-lesson'),'text mode retained in lesson navigation with reading destination');
expect(str_contains($markup,'name="return" value="https://example.test/learning/?reading=text"'),'form redirect retains reading mode');
expect(str_contains($markup,'tabindex="-1" aria-labelledby="habaq-lesson-title"'),'lesson fragment can receive focus and has an accessible name');
expect(str_contains($markup,'aria-label="تقدم دروس المسار"'),'progress has an accessible name');
expect($before===$GLOBALS['meta'],'rendering does not mutate learner records');
$_GET=array('view'=>'library','reading'=>'text','role'=>'photographer','q'=>'صورة','lesson'=>'photo');$markup=Habaq_Learning::render();
expect($GLOBALS['photo_requests']===0,'specialist reading mode also omits image');
expect(str_contains($markup,'view=library&amp;role=photographer&amp;lesson=photo&amp;q='),'image toggle retains allowlisted course and filters');
expect(!str_contains($markup,'"correct"'),'text mode does not expose answer keys');
$_GET=array('view'=>'library','reading'=>'text');$markup=Habaq_Learning::render();
expect(str_contains($markup,'name="reading" value="text"'),'GET search retains text mode');
$_GET=array('reading'=>array('text'),'role'=>array('bad'),'lesson'=>array('bad'),'q'=>array('bad'));
expect(!Habaq_Learning_Library::text_only(),'array reading mode safely ignored');$markup=Habaq_Learning::render();
expect($GLOBALS['photo_requests']===1&&str_contains($markup,'habaq-photo.jpg'),'default photograph preserved');
$_GET=array('reading'=>'text','lesson'=>'unknown','role'=>'unknown','q'=>str_repeat('x',481),'untrusted'=>'secret');
ob_start();Habaq_Learning_Library::reading_controls();$controls=ob_get_clean();
expect(!str_contains($controls,'unknown')&&!str_contains($controls,'secret')&&!str_contains($controls,'q='),'toggle ignores unknown IDs oversized query and unrelated params');
$GLOBALS['caps']=array('read');$_GET=array('reading'=>'text','view'=>'library');
expect(!str_contains(Habaq_Learning::render(),'habaq-learning__hero'),'text mode does not bypass member gate');
// The first editorial batch keeps tested requirements and records while adding useful lesson feedback.
$welcome=Habaq_Learning::module('welcome');$roles=Habaq_Learning::module('roles');
expect($welcome['version']==='2026-10-07.1'&&$roles['version']==='2026-10-07.1','shared editorial versions preserved');
expect($welcome['quiz']['correct']===0&&$roles['quiz']['correct']===1,'shared answer keys preserved');
expect($welcome['title']==='حبق: من أين نبدأ؟'&&str_contains($welcome['body_html'],'سلمى اسم افتراضي'),'welcome title and fictional local example');
expect(str_contains($welcome['body_html'],'هب السويداء مساحة مستقلة'),'Hub independence retained');
expect($roles['title']==='دورك: ما الذي تتولاه، ومن يساعدك؟'&&str_contains($roles['body_html'],'نور اسم افتراضي'),'roles title and fictional work example');
expect(str_contains($roles['job_aid'],'أطلب موافقة قبل')&&str_contains($roles['job_aid'],'عند التسليم'),'roles card captures authority and handover');
$GLOBALS['uid']=1;$GLOBALS['caps']=array('habaq_insider_access');$GLOBALS['meta'][1]=array();
$_GET=array('lesson'=>'welcome','learning_notice'=>'wrong');$markup=Habaq_Learning::render();
expect(str_contains($markup,'فكرة تساعدك')&&str_contains($markup,$welcome['feedback']['retry']),'welcome retry feedback rendered after attempt');
expect(!str_contains($markup,$welcome['feedback']['complete']),'completion feedback hidden before completion');
expect(Habaq_Learning::record(1,$welcome)['status']==='new','retry does not complete rewritten welcome');
submit(array('op'=>'lesson','lesson'=>'welcome','version'=>$welcome['version'],'ack'=>'1','answer'=>'0'));
$_GET=array('lesson'=>'welcome','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$welcome['feedback']['complete'])&&Habaq_Learning::record(1,$welcome)['status']==='complete','welcome completion and explanatory feedback');
submit(array('op'=>'lesson','lesson'=>'roles','version'=>$roles['version'],'ack'=>'1','answer'=>'1'));
$_GET=array('lesson'=>'roles','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$roles['feedback']['complete'])&&Habaq_Learning::record(1,$roles)['status']==='complete','roles completion and explanatory feedback');
expect(!str_contains($markup,'"correct"'),'rewritten lessons still hide raw answer keys');
// The second editorial batch keeps tested requirements while making conduct and data decisions practical.
$conduct=Habaq_Learning::module('conduct');$data=Habaq_Learning::module('data');
expect($conduct['version']==='2026-10-07.1'&&$data['version']==='2026-10-07.1','second shared editorial versions preserved');
expect($conduct['quiz']['correct']===2&&$data['quiz']['correct']===1,'second shared answer keys preserved');
expect($conduct['title']==='نعمل باحترام، ونطلب الموافقة'&&str_contains($conduct['body_html'],'ليان اسم افتراضي'),'conduct title and fictional local example');
expect(str_contains($conduct['body_html'],'الموافقة على حضور لقاء لا تعني الموافقة على التصوير')&&str_contains($conduct['job_aid'],'البديل المستقل'),'conduct separates consent and safe alternate');
expect($data['title']==='أين نحفظ الملفات، ومع من نشاركها؟'&&str_contains($data['body_html'],'ثلاثة ملفات افتراضية'),'data title and three-file exercise');
expect(str_contains($data['body_html'],'لا ترفع ملفاً حقيقياً')&&str_contains($data['job_aid'],'أقل بيانات نحتاجها'),'data exercise minimizes collection');
$GLOBALS['meta'][1]['habaq_learning_conduct']=array('version'=>$conduct['version'],'status'=>'new');
$_GET=array('lesson'=>'conduct','learning_notice'=>'wrong');$markup=Habaq_Learning::render();
expect(str_contains($markup,$conduct['feedback']['retry'])&&!str_contains($markup,$conduct['feedback']['complete']),'conduct retry feedback only after attempt');
expect(Habaq_Learning::record(1,$conduct)['status']==='new','conduct retry does not complete lesson');
$GLOBALS['meta'][1]['habaq_learning_conduct']=array('version'=>$conduct['version'],'status'=>'complete');
$_GET=array('lesson'=>'conduct','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$conduct['feedback']['complete']),'conduct completion principle rendered');
$GLOBALS['meta'][1]['habaq_learning_data']=array('version'=>$data['version'],'status'=>'new');
$_GET=array('lesson'=>'data','learning_notice'=>'wrong');$markup=Habaq_Learning::render();
expect(str_contains($markup,$data['feedback']['retry'])&&!str_contains($markup,'"correct"'),'data retry feedback and hidden key');
$GLOBALS['meta'][1]['habaq_learning_data']=array('version'=>$data['version'],'status'=>'complete');
$_GET=array('lesson'=>'data','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$data['feedback']['complete']),'data completion principle rendered');
// The final shared editorial lesson keeps its tested decision while making a bounded handover practical.
$workflow=Habaq_Learning::module('workflow');
expect($workflow['version']==='2026-10-07.1'&&$workflow['quiz']['correct']===1,'workflow editorial version and answer key preserved');
expect($workflow['title']==='من فكرة صغيرة إلى تسليم واضح'&&str_contains($workflow['body_html'],'ميرا اسم افتراضي'),'workflow title and fictional local example');
expect(str_contains($workflow['body_html'],'لا تحجز مكاناً ولا تنشر إعلاناً')&&str_contains($workflow['body_html'],'حدود القرار'),'workflow example bounds external action');
expect(str_contains($workflow['body_html'],'على ورق أو في ملف نصي خفيف')&&str_contains($workflow['body_html'],'لا تضع بيانات أشخاص أو ميزانية حقيقية'),'workflow supports low connectivity and safe practice');
expect(str_contains($workflow['job_aid'],'تمّ عندما')&&str_contains($workflow['job_aid'],'من يعتمد القرار عند الحاجة')&&str_contains($workflow['job_aid'],'ما بقي والخطوة التالية'),'workflow card supports review authority and handover');
$GLOBALS['meta'][1]['habaq_learning_workflow']=array('version'=>$workflow['version'],'status'=>'new');
$_GET=array('lesson'=>'workflow','learning_notice'=>'wrong');$markup=Habaq_Learning::render();
expect(str_contains($markup,$workflow['feedback']['retry'])&&!str_contains($markup,$workflow['feedback']['complete']),'workflow retry feedback only after attempt');
expect(Habaq_Learning::record(1,$workflow)['status']==='new','workflow retry does not complete lesson');
$GLOBALS['meta'][1]['habaq_learning_workflow']=array('version'=>$workflow['version'],'status'=>'complete');
$_GET=array('lesson'=>'workflow','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$workflow['feedback']['complete'])&&!str_contains($markup,'"correct"'),'workflow completion principle rendered without raw key');
// The media path editorial batch makes the first journalistic task safe and reviewable without changing requirements.
$media_basics=Habaq_Learning::module('media-basics');$media_task=Habaq_Learning::module('media-task');
expect($media_basics['version']==='2026-10-07.1'&&$media_task['version']==='2026-10-07.1','media editorial versions preserved');
expect($media_basics['quiz']['correct']===1&&$media_task['quiz']['correct']===1,'media answer keys preserved');
expect($media_task['assignment']==='قدّم ملخص مقترح القصة وخطة التحقق والحماية، وما الدعم المطلوب. استخدم مثالاً افتراضياً ولا تدرج بيانات أشخاص.','media practical assignment preserved');
expect($media_basics['title']==='بداية العمل الصحفي'&&str_contains($media_basics['body_html'],'ريم اسم افتراضي'),'media introduction and fictional local example');
expect(str_contains($media_basics['body_html'],'قارن بين مقترح ضعيف ومقترح واضح')&&str_contains($media_basics['body_html'],'المصدران اللذان ينقلان عن الشخص نفسه'),'media lesson compares pitches and source independence');
expect(str_contains($media_basics['body_html'],'لا تتصل بمصدر حقيقي قبل اعتماد التكليف')&&str_contains($media_basics['body_html'],'قرار عدم النشر'),'media lesson bounds contact and publishing authority');
expect(str_contains($media_basics['job_aid'],'الادعاء ٢')&&str_contains($media_basics['job_aid'],'تفاصيل قد تكشف الهوية'),'media card supports verification and indirect identification');
expect($media_task['title']==='مهمتك الأولى: مقترح قصة'&&str_contains($media_task['body_html'],'مقترح مكتمل للمقارنة'),'media task has worked pitch');
expect(str_contains($media_task['body_html'],'إرشاد للمراجع')&&str_contains($media_task['body_html'],'تعديلاً واحداً محدداً'),'media task gives actionable reviewer guidance');
expect(str_contains($media_task['body_html'],'لا تضع أسماء أو أرقام تواصل أو شهادات')&&str_contains($media_task['body_html'],'ملف نصي خفيف'),'media task protects data and supports low connectivity');
expect(str_contains($media_task['job_aid'],'ما الذي قد يوقف القصة؟')&&str_contains($media_task['job_aid'],'حدود الاتصال والتسجيل والنشر'),'media task card captures stop and authority boundaries');
$GLOBALS['meta'][1]['habaq_learning_track']='media';
$GLOBALS['meta'][1]['habaq_learning_media-basics']=array('version'=>$media_basics['version'],'status'=>'new');
$_GET=array('lesson'=>'media-basics','learning_notice'=>'wrong');$markup=Habaq_Learning::render();
expect(str_contains($markup,$media_basics['feedback']['retry'])&&!str_contains($markup,$media_basics['feedback']['complete']),'media basics retry feedback only after attempt');
$GLOBALS['meta'][1]['habaq_learning_media-basics']=array('version'=>$media_basics['version'],'status'=>'complete');
$_GET=array('lesson'=>'media-basics','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$media_basics['feedback']['complete']),'media basics completion principle rendered');
$GLOBALS['meta'][1]['habaq_learning_media-task']=array('version'=>$media_task['version'],'status'=>'complete');
$_GET=array('lesson'=>'media-task','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$media_task['feedback']['complete'])&&!str_contains($markup,'"correct"'),'media task review principle rendered without raw key');
// The Habaq People path turns an event idea into one inclusive, reviewable session plan without authorizing delivery.
$people_basics=Habaq_Learning::module('people-basics');$people_task=Habaq_Learning::module('people-task');
expect($people_basics['version']==='2026-10-07.1'&&$people_task['version']==='2026-10-07.1','people editorial versions preserved');
expect($people_basics['quiz']['correct']===1&&$people_task['quiz']['correct']===0,'people answer keys preserved');
expect($people_task['assignment']==='قدّم ملخص خطة الجلسة والجمهور وتوزيع الوقت والأدوار والموارد والمخاطر والتقييم. لا تدرج بيانات حضور حقيقية.','people practical assignment preserved');
expect($people_basics['title']==='بداية العمل في حبق ناس'&&str_contains($people_basics['body_html'],'ديما اسم افتراضي'),'people introduction and fictional local example');
expect(str_contains($people_basics['body_html'],'قارن بين فكرة واسعة وخطة واضحة')&&str_contains($people_basics['body_html'],'لا إعلان ولا حجز ولا صرف'),'people lesson compares plans and bounds external action');
expect(str_contains($people_basics['body_html'],'إذا تعذر احترام الخيار، يتوقف التصوير لا المشاركة')&&str_contains($people_basics['body_html'],'أو يأخذ استراحة'),'people lesson preserves participation without imaging');
expect(str_contains($people_basics['body_html'],'هب السويداء مساحة مستقلة')&&str_contains($people_basics['body_html'],'لا تستخدم أسماء حضور'),'people lesson preserves Hub independence and safe practice');
expect(str_contains($people_basics['job_aid'],'ما لا نستطيع توفيره بعد')&&str_contains($people_basics['job_aid'],'ما يحتاج موافقة قبل الإعلان أو الحجز أو الصرف'),'people card captures barriers and authority');
expect($people_task['title']==='مهمتك الأولى: خطة جلسة'&&str_contains($people_task['body_html'],'خطة مكتملة للمقارنة'),'people task has worked session plan');
expect(str_contains($people_task['body_html'],'إرشاد للمراجع')&&str_contains($people_task['body_html'],'ملاحظة واحدة قابلة للتنفيذ'),'people task gives actionable reviewer guidance');
expect(str_contains($people_task['body_html'],'لا تضع أسماء حضور أو أرقام تواصل')&&str_contains($people_task['body_html'],'ملخصاً نصياً قصيراً'),'people task protects data and supports low connectivity');
expect(str_contains($people_task['job_aid'],'خيارات المشاركة أو الاستراحة')&&str_contains($people_task['job_aid'],'ما الذي لا تثبته النتيجة؟'),'people task card captures inclusion and impact limits');
$GLOBALS['meta'][1]['habaq_learning_track']='people';
$GLOBALS['meta'][1]['habaq_learning_people-basics']=array('version'=>$people_basics['version'],'status'=>'new');
$_GET=array('lesson'=>'people-basics','learning_notice'=>'wrong');$markup=Habaq_Learning::render();
expect(str_contains($markup,$people_basics['feedback']['retry'])&&!str_contains($markup,$people_basics['feedback']['complete']),'people basics retry feedback only after attempt');
$GLOBALS['meta'][1]['habaq_learning_people-basics']=array('version'=>$people_basics['version'],'status'=>'complete');
$_GET=array('lesson'=>'people-basics','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$people_basics['feedback']['complete']),'people basics completion principle rendered');
$GLOBALS['meta'][1]['habaq_learning_people-task']=array('version'=>$people_task['version'],'status'=>'complete');
$_GET=array('lesson'=>'people-task','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$people_task['feedback']['complete'])&&!str_contains($markup,'"correct"'),'people task review principle rendered without raw key');
// The Habaq Production path makes one small prototype reviewable without granting rights or publishing authority.
$production_basics=Habaq_Learning::module('production-basics');$production_task=Habaq_Learning::module('production-task');
expect($production_basics['version']==='2026-10-07.1'&&$production_task['version']==='2026-10-07.1','production editorial versions preserved');
expect($production_basics['quiz']['correct']===2&&$production_task['quiz']['correct']===0,'production answer keys preserved');
expect($production_task['assignment']==='قدّم موجز العمل وخطة الحقوق والموارد والمراجعة، ووصفاً للنسخة التجريبية وأسئلتك للمشرف.','production practical assignment preserved');
expect($production_basics['title']==='بداية العمل في الإنتاج'&&str_contains($production_basics['body_html'],'ليان اسم افتراضي'),'production introduction and fictional local example');
expect(str_contains($production_basics['body_html'],'قارن بين طلب ناقص وموجز واضح')&&str_contains($production_basics['body_html'],'لا نشر ولا شراء معدات'),'production lesson compares briefs and bounds authority');
expect(str_contains($production_basics['body_html'],'لا تحاكِ صوت شخص')&&str_contains($production_basics['body_html'],'موافقة الشخص على الحضور'),'production lesson protects voice and separates consent');
expect(str_contains($production_basics['body_html'],'على الورق')&&str_contains($production_basics['body_html'],'ملف نصي خفيف'),'production lesson supports low connectivity');
expect(str_contains($production_basics['job_aid'],'المادة ٢ ومالكها وإذنها')&&str_contains($production_basics['job_aid'],'ما لا أملك قرار شرائه أو نشره'),'production card captures rights and authority');
expect($production_task['title']==='مهمتك الأولى: نسخة تجريبية'&&str_contains($production_task['body_html'],'مثال مكتمل للمقارنة'),'production task has worked prototype');
expect(str_contains($production_task['body_html'],'إرشاد للمراجع')&&str_contains($production_task['body_html'],'ملاحظة واحدة قابلة للتنفيذ'),'production task gives actionable reviewer guidance');
expect(str_contains($production_task['body_html'],'لا ترفع ملفات مصدر')&&str_contains($production_task['body_html'],'ست جمل مرقمة'),'production task protects raw material and supports weak connections');
expect(str_contains($production_task['job_aid'],'المادة غير الجاهزة أو البديل')&&str_contains($production_task['job_aid'],'ما لا تمنحه هذه المهمة'),'production task card captures exclusions and authority');
$GLOBALS['meta'][1]['habaq_learning_track']='production';
$GLOBALS['meta'][1]['habaq_learning_production-basics']=array('version'=>$production_basics['version'],'status'=>'new');
$_GET=array('lesson'=>'production-basics','learning_notice'=>'wrong');$markup=Habaq_Learning::render();
expect(str_contains($markup,$production_basics['feedback']['retry'])&&!str_contains($markup,$production_basics['feedback']['complete']),'production basics retry feedback only after attempt');
$GLOBALS['meta'][1]['habaq_learning_production-basics']=array('version'=>$production_basics['version'],'status'=>'complete');
$_GET=array('lesson'=>'production-basics','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$production_basics['feedback']['complete']),'production basics completion principle rendered');
$GLOBALS['meta'][1]['habaq_learning_production-task']=array('version'=>$production_task['version'],'status'=>'complete');
$_GET=array('lesson'=>'production-task','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$production_task['feedback']['complete'])&&!str_contains($markup,'"correct"'),'production task review principle rendered without raw key');
// The Radio path keeps its existing requirements while making rights and first review concrete.
$radio_basics=Habaq_Learning::module('radio-basics');$radio_task=Habaq_Learning::module('radio-task');
expect($radio_basics['version']==='2026-10-07.2'&&$radio_task['version']==='2026-10-07.2','radio editorial versions preserved');
expect($radio_basics['quiz']['correct']===0&&$radio_task['quiz']['correct']===2,'radio answer keys preserved');
expect($radio_task['assignment']==='جهز مخطط حلقة قصيرة أو قائمة بث افتراضية بثلاثة أعمال وهمية، مع سجل حقوق وخطة مراجعة وبديل لعمل غير مصرح به.','radio practical assignment preserved');
expect($radio_basics['title']==='بداية العمل في الراديو'&&str_contains($radio_basics['body_html'],'سامر اسم افتراضي'),'radio introduction and fictional local example');
expect(str_contains($radio_basics['body_html'],'قارن بين فكرة واسعة وخطة واضحة')&&str_contains($radio_basics['body_html'],'لا ينشئ اسم المسار فريقاً'),'radio lesson compares plans and avoids invented role');
expect(str_contains($radio_basics['body_html'],'البث المباشر أو الخطي')&&str_contains($radio_basics['body_html'],'لا تعتبر خانة فارغة موافقة'),'radio lesson distinguishes rights scopes');
expect(str_contains($radio_basics['body_html'],'على ورق أو في ملف نصي خفيف')&&str_contains($radio_basics['body_html'],'لا تستخدم أسماء فنانين'),'radio lesson supports low connectivity and safe practice');
expect(str_contains($radio_basics['job_aid'],'المدة وطريقة الإنهاء')&&str_contains($radio_basics['job_aid'],'ما لا أملك قرار بثه'),'radio role card captures rights and authority');
expect($radio_task['title']==='مهمتك الأولى: مخطط حلقة'&&str_contains($radio_task['body_html'],'مثال مكتمل للمقارنة'),'radio task has worked episode plan');
expect(str_contains($radio_task['body_html'],'العمل «ب» يحتاج إذناً منفصلاً للأرشيف')&&str_contains($radio_task['body_html'],'ضع «غير جاهز»'),'radio task models rights state and fallback');
expect(str_contains($radio_task['body_html'],'إرشاد للمراجع')&&str_contains($radio_task['body_html'],'ملاحظة واحدة قابلة للتنفيذ'),'radio task gives actionable reviewer guidance');
expect(str_contains($radio_task['body_html'],'لا ترفع ملفات موسيقية')&&str_contains($radio_task['body_html'],'بطاقة نصية من سبعة أسطر'),'radio task protects files and supports weak connection');
expect(str_contains($radio_task['job_aid'],'العمل ٣ والنسخة والحق')&&str_contains($radio_task['job_aid'],'ما يحتاج اعتماداً منفصلاً'),'radio task card captures three works and separate authority');
$GLOBALS['meta'][1]['habaq_learning_track']='radio';
$GLOBALS['meta'][1]['habaq_learning_radio-basics']=array('version'=>$radio_basics['version'],'status'=>'new');
$_GET=array('lesson'=>'radio-basics','learning_notice'=>'wrong');$markup=Habaq_Learning::render();
expect(str_contains($markup,$radio_basics['feedback']['retry'])&&!str_contains($markup,$radio_basics['feedback']['complete']),'radio basics retry feedback only after attempt');
$GLOBALS['meta'][1]['habaq_learning_radio-basics']=array('version'=>$radio_basics['version'],'status'=>'complete');
$_GET=array('lesson'=>'radio-basics','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$radio_basics['feedback']['complete']),'radio basics completion principle rendered');
$GLOBALS['meta'][1]['habaq_learning_radio-task']=array('version'=>$radio_task['version'],'status'=>'complete');
$_GET=array('lesson'=>'radio-task','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$radio_task['feedback']['complete'])&&!str_contains($markup,'"correct"'),'radio task review principle rendered without raw key');
// The Finance path makes one fictional operation file reviewable without granting approval or payment authority.
$finance_basics=Habaq_Learning::module('finance-basics');$finance_task=Habaq_Learning::module('finance-task');
expect($finance_basics['version']==='2026-10-07.2'&&$finance_task['version']==='2026-10-07.2','finance editorial versions preserved');
expect($finance_basics['quiz']['correct']===0&&$finance_task['quiz']['correct']===2,'finance answer keys preserved');
expect($finance_task['assignment']==='جهز ملف عملية ومطابقة افتراضيين بخمس حركات وميزانية صغيرة، مع قائمة نقص ومالك معالجة. لا تستخدم كشفاً أو بيانات دفع حقيقية.','finance practical assignment preserved');
expect($finance_basics['title']==='بداية العمل في المالية'&&str_contains($finance_basics['body_html'],'ريم اسم افتراضي'),'finance introduction and fictional local example');
expect(str_contains($finance_basics['body_html'],'قارن بين طلب ناقص وطلب واضح')&&str_contains($finance_basics['body_html'],'التمويل متوقع ولم يُقبض بعد'),'finance lesson compares requests and separates expected funding');
expect(str_contains($finance_basics['body_html'],'الفاتورة ليست الملف كله')&&str_contains($finance_basics['body_html'],'لا يعتمد الشخص طلبه وحده'),'finance lesson separates evidence and approval');
expect(str_contains($finance_basics['body_html'],'على ورق أو في ملف نصي خفيف')&&str_contains($finance_basics['body_html'],'لا تستخدم مبلغاً حقيقياً'),'finance lesson supports low connectivity and safe practice');
expect(str_contains($finance_basics['job_aid'],'حالة التمويل')&&str_contains($finance_basics['job_aid'],'ما لا أملك قرار توقيعه أو دفعه'),'finance role card captures liquidity and authority');
expect($finance_task['title']==='مهمتك الأولى: ملف عملية مالية'&&str_contains($finance_task['body_html'],'مثال مكتمل للمقارنة'),'finance task has worked operation file');
expect(str_contains($finance_task['body_html'],'الحركة ٥')&&str_contains($finance_task['body_html'],'نسخة مكررة'),'finance task models five movements and duplicate detection');
expect(str_contains($finance_task['body_html'],'إرشاد للمراجع')&&str_contains($finance_task['body_html'],'تعديلاً واحداً قابلاً للتنفيذ'),'finance task gives actionable reviewer guidance');
expect(str_contains($finance_task['body_html'],'لا تنفذ دفعة')&&str_contains($finance_task['body_html'],'سبعة أسطر فقط'),'finance task blocks real payment and supports weak connection');
expect(str_contains($finance_task['job_aid'],'الحركة ٥')&&str_contains($finance_task['job_aid'],'من يراجع الدفع'),'finance task card captures movements and payment review');
$GLOBALS['meta'][1]['habaq_learning_track']='finance';
$GLOBALS['meta'][1]['habaq_learning_finance-basics']=array('version'=>$finance_basics['version'],'status'=>'new');
$_GET=array('lesson'=>'finance-basics','learning_notice'=>'wrong');$markup=Habaq_Learning::render();
expect(str_contains($markup,$finance_basics['feedback']['retry'])&&!str_contains($markup,$finance_basics['feedback']['complete']),'finance basics retry feedback only after attempt');
$GLOBALS['meta'][1]['habaq_learning_finance-basics']=array('version'=>$finance_basics['version'],'status'=>'complete');
$_GET=array('lesson'=>'finance-basics','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$finance_basics['feedback']['complete']),'finance basics completion principle rendered');
$GLOBALS['meta'][1]['habaq_learning_finance-task']=array('version'=>$finance_task['version'],'status'=>'complete');
$_GET=array('lesson'=>'finance-task','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$finance_task['feedback']['complete'])&&!str_contains($markup,'"correct"'),'finance task review principle rendered without raw key');
// The People Operations path turns onboarding into one safe, bounded plan without inventing appointments or collecting HR records.
$people_ops_basics=Habaq_Learning::module('people-ops-basics');$people_ops_task=Habaq_Learning::module('people-ops-task');
expect($people_ops_basics['version']==='2026-10-07.2'&&$people_ops_task['version']==='2026-10-07.2','people operations editorial versions preserved');
expect($people_ops_basics['quiz']['correct']===0&&$people_ops_task['quiz']['correct']===2,'people operations answer keys preserved');
expect($people_ops_task['assignment']==='جهز خطة انضمام لشخص افتراضي تشمل الدور والوقت ومرافقاً بصفته ومهمة صغيرة ومعايير ودعماً ومراجعة وخروجاً منظماً.','people operations practical assignment preserved');
expect($people_ops_basics['title']==='بداية العمل في دعم الأشخاص'&&str_contains($people_ops_basics['body_html'],'مايا اسم افتراضي لشخص جديد من السويداء'),'people operations introduction and fictional local example');
expect(str_contains($people_ops_basics['body_html'],'قارن بين بداية مربكة وبداية واضحة')&&str_contains($people_ops_basics['body_html'],'ستة نصوص عامة'),'people operations lesson compares unclear and bounded onboarding');
expect(str_contains($people_ops_basics['body_html'],'الشكوى أو الإفادة الحساسة')&&str_contains($people_ops_basics['body_html'],'له مسار مستقل'),'people operations lesson separates learning support from complaints');
expect(str_contains($people_ops_basics['body_html'],'لا نفترض أنها موظفة أو متطوعة')&&str_contains($people_ops_basics['body_html'],'لا يعتمد سياسة'),'people operations lesson avoids invented relationship and policy adoption');
expect(str_contains($people_ops_basics['body_html'],'على ورق أو في ملف نصي خفيف')&&str_contains($people_ops_basics['body_html'],'بديل عند ضعف الاتصال'),'people operations lesson supports low connectivity');
expect(str_contains($people_ops_basics['job_aid'],'المرافق بصفته')&&str_contains($people_ops_basics['job_aid'],'ما يتوقف ويذهب إلى مسار مستقل'),'people operations start card captures support and escalation limits');
expect($people_ops_task['title']==='مهمتك الأولى: خطة انضمام'&&str_contains($people_ops_task['body_html'],'مثال مكتمل للمقارنة'),'people operations task has a worked onboarding plan');
expect(str_contains($people_ops_task['body_html'],'45 إلى 60 دقيقة')&&str_contains($people_ops_task['body_html'],'نقطة تحقق في منتصف الفترة'),'people operations task stays small and reviewable');
expect(str_contains($people_ops_task['body_html'],'إرشاد للمراجع')&&str_contains($people_ops_task['body_html'],'تعديلاً واحداً قابلاً للتنفيذ'),'people operations task gives actionable reviewer guidance');
expect(str_contains($people_ops_task['body_html'],'سبعة أسطر فقط')&&str_contains($people_ops_task['body_html'],'لا تستخدم اسماً حقيقياً'),'people operations task supports safe low-connectivity submission');
expect(str_contains($people_ops_task['body_html'],'الخروج المنظم')&&str_contains($people_ops_task['job_aid'],'إلغاء الوصول غير اللازم'),'people operations task covers safe handover and exit');
$GLOBALS['meta'][1]['habaq_learning_track']='people-ops';
$GLOBALS['meta'][1]['habaq_learning_people-ops-basics']=array('version'=>$people_ops_basics['version'],'status'=>'new');
$_GET=array('lesson'=>'people-ops-basics','learning_notice'=>'wrong');$markup=Habaq_Learning::render();
expect(str_contains($markup,$people_ops_basics['feedback']['retry'])&&!str_contains($markup,$people_ops_basics['feedback']['complete']),'people operations basics retry feedback only after attempt');
$GLOBALS['meta'][1]['habaq_learning_people-ops-basics']=array('version'=>$people_ops_basics['version'],'status'=>'complete');
$_GET=array('lesson'=>'people-ops-basics','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$people_ops_basics['feedback']['complete']),'people operations basics completion principle rendered');
$GLOBALS['meta'][1]['habaq_learning_people-ops-task']=array('version'=>$people_ops_task['version'],'status'=>'complete');
$_GET=array('lesson'=>'people-ops-task','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$people_ops_task['feedback']['complete'])&&!str_contains($markup,'"correct"'),'people operations task review principle rendered without raw key');
// The first path editorial batch makes operations work concrete without changing tested requirements.
$operations_basics=Habaq_Learning::module('operations-basics');$operations_task=Habaq_Learning::module('operations-task');
expect($operations_basics['version']==='2026-10-07.2'&&$operations_task['version']==='2026-10-07.2','operations editorial versions preserved');
expect($operations_basics['quiz']['correct']===0&&$operations_task['quiz']['correct']===2,'operations answer keys preserved');
expect($operations_basics['title']==='بداية العمل في التنسيق'&&str_contains($operations_basics['body_html'],'هالة، وهي شخصية افتراضية من السويداء'),'operations introduction and fictional local example');
expect(str_contains($operations_basics['body_html'],'صاحب المهمة')&&str_contains($operations_basics['body_html'],'صاحب القرار')&&str_contains($operations_basics['body_html'],'لا يعتمد ميزانية'),'operations role separates ownership review and authority');
expect(str_contains($operations_basics['job_aid'],'تمّت المهمة عندما')&&str_contains($operations_basics['job_aid'],'لا أملك قرار'),'operations role card captures completion and limits');
expect($operations_task['title']==='مهمتك الأولى: التنسيق'&&str_contains($operations_task['body_html'],'لوحة أسبوع مكتملة للمقارنة'),'operations task has worked board');
expect(str_contains($operations_task['body_html'],'إرشاد للمراجع')&&str_contains($operations_task['body_html'],'ملاحظة واحدة قابلة للتنفيذ'),'operations task gives bounded reviewer guidance');
expect(str_contains($operations_task['body_html'],'لا تنسخ ملفات المشروع')&&str_contains($operations_task['body_html'],'غير مؤكد'),'operations task protects data and marks uncertain resources');
expect(str_contains($operations_task['job_aid'],'مهمة ٣')&&str_contains($operations_task['job_aid'],'القرار المعلق وصاحبه وموعده'),'operations task card supports three tasks and decision log');
$GLOBALS['meta'][1]['habaq_learning_track']='operations';
$GLOBALS['meta'][1]['habaq_learning_operations-basics']=array('version'=>$operations_basics['version'],'status'=>'new');
$_GET=array('lesson'=>'operations-basics','learning_notice'=>'wrong');$markup=Habaq_Learning::render();
expect(str_contains($markup,$operations_basics['feedback']['retry'])&&!str_contains($markup,$operations_basics['feedback']['complete']),'operations basics retry feedback only after attempt');
$GLOBALS['meta'][1]['habaq_learning_operations-basics']=array('version'=>$operations_basics['version'],'status'=>'complete');
$_GET=array('lesson'=>'operations-basics','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$operations_basics['feedback']['complete']),'operations basics completion principle rendered');
$GLOBALS['meta'][1]['habaq_learning_operations-task']=array('version'=>$operations_task['version'],'status'=>'complete');
$_GET=array('lesson'=>'operations-task','learning_notice'=>'saved');$markup=Habaq_Learning::render();
expect(str_contains($markup,$operations_task['feedback']['complete'])&&!str_contains($markup,'"correct"'),'operations task review principle rendered without raw key');
echo 'Passed '.$count." behavioral checks.\n";
