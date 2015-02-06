<?php

function first_install() {
    
    /*
     *
     * Insert a course
     *  
     */
    $data = new stdClass();
    $course_author = get_current_user_ID();
    $course_title = 'Aenean auctor nec magna sed mattis';
    $course_excerpt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin varius enim hendrerit tincidunt hendrerit. Duis sem justo, eleifend vel pellentesque ut, tristique eu quam.';
    $course_content = 'Fusce non consectetur magna. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Suspendisse potenti. Curabitur placerat dolor cursus imperdiet tincidunt. Quisque velit lacus, egestas id velit iaculis, hendrerit accumsan lectus. Curabitur hendrerit ut dolor quis scelerisque. Mauris at diam eu ipsum consequat malesuada. Pellentesque nulla nisi, hendrerit id luctus quis, dignissim sit amet ipsum. Nam aliquam odio nibh, non accumsan metus tincidunt nec.

Nulla commodo pharetra auctor. Ut suscipit imperdiet orci eu pellentesque. Aliquam at faucibus nibh, accumsan fermentum libero. Praesent eleifend nibh lectus, quis pharetra dolor aliquam sit amet. Sed euismod urna lacus, nec pulvinar mi vulputate ac. Integer semper eget ipsum adipiscing vestibulum. Mauris quis mauris mollis, commodo lorem eu, fermentum risus. Fusce condimentum turpis id congue eleifend. Ut sagittis ultrices sem, quis pulvinar magna tincidunt eu. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Cras at faucibus nibh. Donec posuere a dui in porttitor. Ut vel arcu in mauris gravida venenatis. Maecenas scelerisque, lectus at sodales vestibulum, tortor elit varius nibh, sit amet euismod purus libero nec sem. Nulla lectus nisi, dictum nec libero at, sollicitudin cursus ante. Pellentesque at faucibus felis, dignissim aliquet turpis.

Integer id erat arcu. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed porttitor, mauris at pellentesque gravida, enim ipsum semper diam, vel sodales augue orci eu sem. Nulla tempor sodales felis sed posuere. Quisque accumsan, quam at feugiat porttitor, nunc nulla imperdiet lectus, vel sodales quam libero in mi. Ut lacinia ipsum quis placerat sodales. Interdum et malesuada fames ac ante ipsum primis in faucibus. Aenean ullamcorper elementum eros, vel scelerisque diam sodales in. Proin vehicula magna augue, sed posuere neque rhoncus ac. Sed nec pharetra dolor. Proin enim felis, volutpat in condimentum vitae, euismod ut massa. Donec sollicitudin felis sed orci placerat, in scelerisque augue bibendum. Vivamus nisl lectus, aliquam ut elit sit amet, ullamcorper fringilla sapien. Donec rutrum id urna at venenatis. Vestibulum et diam mollis, consequat mi nec, iaculis nulla.

Morbi posuere semper quam, at hendrerit libero blandit quis. Sed a facilisis metus. Donec at suscipit augue. Curabitur in egestas nisl. Nunc et dictum diam, eget commodo est. Suspendisse eget dignissim tellus, euismod auctor ante. Fusce posuere sollicitudin libero, in gravida urna tincidunt volutpat. Nam in lectus purus. Sed nec quam eu dolor luctus volutpat. Phasellus molestie lobortis mauris, nec posuere lacus convallis eu. Proin dapibus nisl tortor. Cras vulputate bibendum lectus eu rutrum.

Sed vehicula velit at sem sollicitudin malesuada. Praesent imperdiet elementum orci sit amet ornare. Nunc eu tincidunt tellus, quis condimentum nisi. Quisque in urna at nibh pharetra molestie id imperdiet tortor. Donec ut fringilla dui, eget fringilla quam. Curabitur sodales justo libero, porta convallis ligula sodales sit amet. Morbi vel dolor nulla.

Nunc a sapien facilisis, gravida felis nec, fermentum odio. Integer purus turpis, pharetra et tempor quis, aliquam id mi. Duis eleifend condimentum tempor. Nunc arcu nibh, vehicula ut iaculis non, pulvinar ac massa. Donec tempus lectus quis aliquet laoreet. Nam hendrerit, massa eget suscipit commodo, magna est auctor sapien, in consequat lorem nisl eu libero. Aliquam erat volutpat. In sit amet massa a lectus lobortis porta. Suspendisse potenti. Mauris a tellus pharetra, mollis massa vitae, congue nisi. Vestibulum nec pulvinar nisl. Vivamus fermentum, tortor id rutrum pretium, eros dolor ultrices orci, non eleifend diam massa a arcu. Quisque vulputate interdum risus vel rutrum. Curabitur magna nibh, dictum vitae feugiat at, hendrerit ac purus.

Proin fermentum odio odio, consectetur blandit orci vestibulum non. Suspendisse a porttitor tortor. Proin vitae ipsum sed ipsum vestibulum molestie. Fusce mattis massa sed aliquet fermentum. Nullam cursus, nibh vel eleifend auctor, orci enim porttitor quam, ut facilisis sem quam vel lectus. Pellentesque et odio ut tellus semper congue. Vivamus imperdiet, urna eget vehicula auctor, tortor massa aliquam purus, sed tristique dolor lacus et orci.

Curabitur dictum, sem sit amet tincidunt laoreet, felis felis pulvinar purus, vel convallis orci sapien eget libero. Aenean turpis tortor, blandit sed dictum sit amet, rutrum vel magna. Suspendisse non dui lacus. Praesent congue arcu elit. Phasellus vulputate massa elit. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Morbi blandit ultrices semper. Duis eros risus, bibendum sed mi vitae, pharetra lacinia tellus. Maecenas neque nisl, rutrum a leo eu, laoreet ultrices ante. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla in nisi at nunc rhoncus dictum tempus ut arcu.

Pellentesque molestie venenatis quam vitae suscipit. Mauris odio odio, commodo et imperdiet non, elementum ac turpis. Aenean et ipsum purus. Nullam augue purus, commodo in mi sollicitudin, vehicula sagittis erat. Aliquam nec eleifend urna. Donec quis viverra enim. Cras arcu mi, mattis at feugiat eu, sagittis ac velit. Vestibulum velit ligula, pellentesque id vestibulum a, tincidunt eget mi. Proin nec vestibulum libero, in iaculis enim. Vivamus eget gravida ante, a iaculis libero. Morbi ultricies mollis ligula, nec lacinia tellus aliquet auctor. In hac habitasse platea dictumst. Suspendisse a varius tellus. Aenean tincidunt, dolor sit amet feugiat iaculis, risus nulla gravida arcu, sed tincidunt orci orci a libero.

Aenean posuere leo sit amet magna bibendum dictum. Cras vel sodales nulla. In hac habitasse platea dictumst. Maecenas fringilla mollis sem, sed tempus ipsum tristique eu. Sed tincidunt porta dolor, in tincidunt justo viverra in. Donec mollis libero urna, eu egestas leo sollicitudin non. Nulla tristique nulla nec volutpat elementum. Ut faucibus, sem sit amet egestas gravida, tellus dolor porttitor lorem, nec lobortis metus odio ut ante. Integer et purus mollis dui suscipit rutrum vel vel tortor. Sed eget quam felis. Aenean aliquam ac orci sit amet bibendum.

Ut lectus neque, gravida ac eleifend vel, sagittis at lectus. Proin facilisis vulputate lacus vitae ornare. Nunc justo dui, eleifend ac justo non, pulvinar dictum mauris. Quisque egestas posuere arcu nec cursus. Suspendisse malesuada augue eget mi laoreet, sit amet volutpat justo imperdiet. Morbi lobortis molestie tellus. Nunc ultrices ultrices felis, porttitor tempor nisl fermentum lobortis. Vivamus vitae justo vitae massa facilisis venenatis et et diam.

Integer sollicitudin felis sed dolor viverra mattis. Nullam rutrum nulla nisi, id vulputate ligula interdum id. Morbi orci mi, vulputate et pulvinar sit amet, vestibulum sit amet turpis. In hac habitasse platea dictumst. Curabitur sed justo mauris. Maecenas eget imperdiet nunc, id tempor mauris. Quisque tempus mauris ac lorem sollicitudin viverra. Maecenas semper odio non magna egestas hendrerit. Nunc vel massa dapibus, consequat risus et, lacinia nulla. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aliquam eros urna, interdum sagittis leo sit amet, ullamcorper fermentum risus. Maecenas sed eros odio.

Nullam iaculis justo eget hendrerit consequat. Nullam vestibulum ligula nec nulla lacinia vestibulum. Duis aliquet egestas diam ac consequat. Aliquam erat volutpat. Sed auctor arcu lorem, id interdum augue eleifend a. Mauris luctus, lorem quis scelerisque accumsan, justo risus mattis erat, vel ultrices lacus tortor nec ipsum. Maecenas sit amet convallis metus, vel vulputate velit. Integer mi nibh, vehicula a pretium id, euismod ac felis. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Quisque accumsan libero neque, eget elementum erat pellentesque sit amet. Nunc aliquam, arcu a rutrum blandit, nibh orci facilisis mi, commodo euismod nulla tellus vitae magna. Pellentesque vestibulum neque sit amet ipsum pharetra, dignissim pulvinar augue imperdiet. Cras vehicula at mauris ac pulvinar. Mauris at lectus felis. Aenean blandit ante ultrices, interdum nibh a, molestie sem. Nam porttitor, justo in pellentesque rhoncus, sem risus rutrum justo, vel iaculis sapien magna semper elit.

Vestibulum egestas, felis ac dictum fermentum, ante augue sagittis eros, nec varius mi libero id neque. Morbi ac eleifend ipsum. Fusce pharetra scelerisque pharetra. Mauris vulputate leo ligula, non ornare sem varius non. Integer est leo, semper non sapien at, dignissim vestibulum sem. Morbi sit amet egestas justo. Maecenas dignissim euismod elit et iaculis. Mauris lobortis, neque ut luctus facilisis, lorem tortor dictum ipsum, eu laoreet quam magna in nisi. Pellentesque in tincidunt ligula, sit amet porta ipsum. Suspendisse faucibus sollicitudin feugiat. Donec imperdiet at ipsum ac convallis. Aenean gravida neque quis lacus commodo convallis. Morbi scelerisque orci a massa blandit, at placerat sem laoreet. Pellentesque pharetra ultrices justo, ut aliquam dolor ullamcorper at. Nunc quis quam tincidunt, sollicitudin risus vitae, tempor purus. Vestibulum interdum enim purus, sed lacinia libero fringilla quis.

Fusce suscipit tincidunt libero, vitae rutrum neque mollis eu. Morbi congue elit eget enim vehicula, convallis vehicula leo gravida. Nulla facilisi. Vestibulum facilisis odio luctus condimentum congue. Nam blandit odio nec enim porttitor, vel cursus massa ornare. Quisque ac odio id est interdum pellentesque id quis diam. Sed dui urna, molestie non tempor eu, porttitor a magna. Aliquam eu nulla sed mauris feugiat sollicitudin. Suspendisse eget viverra mauris. Donec aliquet aliquam enim, id fringilla felis porttitor at. Nullam pellentesque dictum risus id dignissim. Donec interdum, justo nec dictum cursus, lectus enim dignissim enim, vel pharetra urna justo sit amet diam. Proin tempor eu nunc et interdum. Nullam non odio eget velit hendrerit pulvinar ut vitae sem. Pellentesque pulvinar vulputate est, et faucibus lorem placerat quis.

Morbi eleifend, urna non varius viverra, tellus nulla auctor neque, quis lobortis mauris justo at quam. Sed at elit lorem. Mauris nisl orci, pretium at quam vitae, elementum blandit nunc. Duis congue orci non facilisis rhoncus. Praesent ut tempus quam. Cras id varius eros. Curabitur id magna tellus. Vestibulum porttitor placerat pretium. Nulla a dolor non felis euismod congue. Vestibulum fermentum varius tortor, et malesuada nunc gravida in. Vivamus eleifend, eros a fermentum porta, ante dui ullamcorper libero, ultrices commodo quam magna sed risus. Vivamus semper dui blandit consequat tempor. Morbi justo risus, congue a urna a, sodales vestibulum eros. Morbi ligula velit, auctor vitae dictum tempus, rutrum ut nibh. Vivamus a diam eget est lacinia vestibulum non vel sem.

Morbi id sapien sodales, fringilla felis a, cursus ante. Suspendisse interdum, enim ac auctor sodales, magna ipsum pulvinar turpis, in vulputate dui neque et eros. Duis neque elit, blandit sed velit id, lobortis tempus dui. Aenean porttitor, neque sit amet placerat aliquam, mi arcu tincidunt elit, at placerat odio neque fringilla neque. Integer blandit nisl ut ornare placerat. Aenean molestie, ipsum et vulputate aliquam, lorem justo placerat elit, eget scelerisque nulla nulla vel lectus. Praesent tincidunt tellus quis rutrum porttitor. Duis quis tincidunt tortor, eu fermentum neque. Vivamus cursus vel lectus eu vehicula. Maecenas commodo euismod mauris, et laoreet quam. Duis quis ultrices felis. Donec imperdiet nulla nec risus tempor, sit amet accumsan quam sagittis. Integer vitae adipiscing tortor.

Suspendisse tincidunt ligula venenatis dolor fringilla, ut vulputate enim pharetra. Vivamus vel congue erat, sit amet euismod sapien. Aenean mattis pulvinar mattis. Nunc posuere neque eu tempor varius. Praesent interdum quam quam, sit amet euismod ligula lobortis ac. Cras dictum tortor eros, ac congue risus scelerisque in. Etiam vestibulum libero sit amet massa venenatis, eget blandit nibh suscipit. Pellentesque egestas ultrices laoreet. Nullam nec justo at magna ultricies ultricies. Maecenas sit amet faucibus orci.

Aliquam erat volutpat. Donec scelerisque consequat risus, ut porta diam vestibulum ac. Vivamus vehicula dictum diam. Nulla consectetur diam leo, sit amet porttitor nulla porttitor vitae. Nunc nisi tortor, fringilla nec congue non, molestie sit amet risus. Maecenas lorem nisl, vestibulum nec leo non, commodo convallis sapien. Nulla facilisi. Sed pulvinar arcu sed dui ultricies ullamcorper. Maecenas a nunc eget est fermentum interdum. Aenean molestie, risus quis feugiat ornare, est erat bibendum mauris, a commodo mauris est et justo. Donec auctor sapien in urna imperdiet, eget fermentum nunc pulvinar. Sed eu eros vehicula, pulvinar justo ut, lacinia lectus. Aenean suscipit justo et iaculis venenatis. In hac habitasse platea dictumst. Nunc blandit ligula id est luctus ultricies.

Phasellus non tellus vitae massa mollis mollis in vel massa. Maecenas neque mauris, varius eu porta in, euismod sed tortor. Pellentesque sem quam, mattis non lacus eget, molestie rutrum lorem. Morbi id eros mattis, blandit justo at, eleifend orci. Sed eget felis id eros sodales fringilla non eu lectus. Etiam et euismod massa. Morbi elementum ut purus at adipiscing.

Proin vel semper augue. Pellentesque vel metus id nibh convallis imperdiet. Vivamus euismod rhoncus metus ut consequat. Quisque et purus nisi. Aliquam erat volutpat. Phasellus at egestas justo. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi vitae leo eget nibh consequat convallis. Aliquam erat volutpat. Etiam dignissim, elit vel pretium rhoncus, eros lectus convallis mi, ut facilisis lorem neque ac ligula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nullam ullamcorper, quam sed faucibus varius, odio lorem luctus elit, vitae egestas elit ipsum ac quam. Suspendisse eget volutpat est.

Integer et ante accumsan augue vulputate venenatis. Aenean ac enim purus. Nullam tempor orci id erat egestas, quis porttitor dui gravida. Curabitur non sem vel ipsum fermentum auctor et vel orci. Sed adipiscing placerat bibendum. Etiam vel porttitor augue. Ut ante purus, laoreet ac velit sit amet, pellentesque malesuada odio. Suspendisse pellentesque facilisis dictum. Integer vel iaculis mauris. Mauris dignissim est vitae sem gravida, ac faucibus magna consectetur. Vivamus in mauris in ipsum molestie dictum in at enim. Cras nisi lorem, placerat vitae urna eu, interdum lobortis sem. Suspendisse potenti. Vivamus vel venenatis metus.

Nunc fermentum fringilla tortor, et iaculis erat iaculis sit amet. Donec ultrices imperdiet quam. Aliquam pellentesque venenatis sodales. Mauris elementum mauris a volutpat ullamcorper. Duis suscipit tellus enim, nec sollicitudin dui tempor ut. Mauris sodales nisi ut laoreet ullamcorper. Nam pharetra luctus elit, vel dapibus erat. Aenean placerat lectus ante, et euismod eros congue et. Integer fringilla, dolor in mattis varius, felis dolor aliquam tortor, in cursus augue nisi in mi. Praesent egestas lectus suscipit arcu volutpat lacinia. Nam fermentum porttitor turpis ut vestibulum. Aenean tristique metus odio, eu rutrum arcu tristique at.

Quisque malesuada suscipit dolor et pharetra. Morbi semper lectus id consequat consectetur. Phasellus vitae laoreet velit, in aliquam erat. Nulla facilisi. Fusce mi ante, ultricies ac nunc sed, congue posuere tellus. Suspendisse non turpis et neque gravida ultricies ut ut risus. Nunc interdum lorem vitae nisi fringilla, et aliquet nulla iaculis. Donec felis diam, lacinia ac ante at, auctor vehicula mi.

Vestibulum posuere quam ut enim condimentum, sit amet fermentum libero porta. Aliquam erat volutpat. Nulla in pretium lacus. Ut porta ipsum eget mollis pretium. Fusce sit amet ante nibh. Mauris imperdiet nulla eu elit fringilla consequat. Suspendisse potenti. Integer bibendum purus ac lacus tempus vulputate. Proin lobortis et justo quis mattis. Proin commodo tellus lectus, eu facilisis massa condimentum at.

Vivamus quis suscipit tellus. Sed laoreet euismod venenatis. Praesent eget fringilla purus. Etiam quis ligula non quam posuere mollis. Phasellus euismod sem enim, eget tincidunt mauris consectetur auctor. Aliquam vitae vehicula quam. Sed feugiat, massa sed auctor vestibulum, orci nibh aliquam tellus, eget dapibus leo nibh quis sem. Aliquam feugiat tristique elit, nec rutrum nunc cursus nec. Donec bibendum quis libero at congue. Fusce dictum justo id ligula auctor viverra.

Curabitur enim nisi, luctus vehicula condimentum convallis, pharetra a diam. Aenean rutrum cursus justo ut feugiat. Duis volutpat justo ipsum, sit amet viverra justo ullamcorper et. Proin vel nisi nibh. Nunc eget ligula vitae est bibendum mattis. Vestibulum rhoncus euismod odio quis faucibus. Sed malesuada vehicula ultrices.

Vivamus dui mi, commodo eu sagittis sit amet, pretium eget felis. Vestibulum diam nunc, ultricies ultricies metus sit amet, convallis viverra ipsum. Aenean ac consequat nisi. Donec pellentesque hendrerit odio sit amet pretium. Aenean condimentum elit eros, vel egestas augue congue ac. Sed porta lacus sed diam tempus laoreet. Nam facilisis gravida libero, et adipiscing elit tincidunt non. Praesent porta leo at velit semper, sit amet sollicitudin turpis imperdiet. Aliquam purus metus, convallis ut faucibus nec, laoreet a dui. Ut consequat est eu tempor laoreet. Suspendisse nec bibendum orci. Nam rhoncus risus ac euismod imperdiet. Pellentesque ultrices ipsum eget sem scelerisque, nec auctor dolor laoreet. Vestibulum volutpat elementum tortor, et posuere ipsum mattis non. Suspendisse id pulvinar odio.

Fusce sodales dolor et ipsum rutrum fringilla. Phasellus congue nunc elit, eget rhoncus neque luctus at. Nullam elementum quis est et congue. Phasellus volutpat, est accumsan hendrerit ultrices, justo magna pulvinar libero, sed varius turpis odio sed mi. Morbi non pretium mi. Phasellus dapibus tortor eu nunc gravida, ac rhoncus lectus malesuada. Aliquam vestibulum erat eget ornare eleifend.

In dictum lectus nec risus pulvinar accumsan. Suspendisse non nibh auctor ipsum ultrices viverra id ac dui. Suspendisse enim orci, sodales suscipit odio a, semper tempus eros. Donec vel ligula sed urna pretium porttitor eu et enim. In scelerisque elementum felis vitae tincidunt. Nulla pellentesque, dolor ac feugiat commodo, ipsum nisi pharetra metus, in consectetur odio turpis in est. Duis a lectus lorem. Sed scelerisque libero a fermentum cursus. Phasellus eu lacus et lorem commodo rutrum. Vivamus turpis turpis, sollicitudin vitae leo condimentum, accumsan ultrices enim. Vivamus non lacus lobortis magna eleifend varius id in magna. Proin in elit hendrerit, ultricies purus vitae, ultrices dui. Nullam feugiat vulputate justo ac vestibulum. Nunc ultrices consectetur quam.

Maecenas sodales tincidunt sagittis. Quisque at nunc nec leo aliquam sollicitudin. Donec eget sollicitudin quam. Donec id mi quis velit iaculis luctus sit amet scelerisque felis. Interdum et malesuada fames ac ante ipsum primis in faucibus. Fusce congue ut ipsum nec mattis. Quisque egestas placerat aliquet. Aliquam aliquam commodo laoreet. Nunc id lacus in sapien sagittis euismod vel dignissim nunc. Duis augue lorem, accumsan vel lorem eget, bibendum dictum diam. Morbi vulputate euismod odio sed aliquet. In vehicula vitae tellus a sodales. Nam ac varius libero. Nulla sit amet vulputate ante.

Nam ut ornare ipsum. Integer pretium nisl est, vel gravida dolor mollis non. Ut consequat lectus vitae metus vehicula, quis ultricies velit mollis. Quisque sodales sodales tellus nec dignissim. Aenean id ipsum sit amet augue consequat interdum. Etiam tristique vehicula eros eu faucibus. Proin scelerisque magna sit amet magna convallis placerat.

Sed egestas erat nec purus sollicitudin, vel elementum dolor blandit. Praesent interdum nulla quis augue accumsan, vel pulvinar ligula cursus. Curabitur id nisi nisl. Proin aliquam lorem id urna molestie, non eleifend diam interdum. Praesent ultricies metus lorem, quis tristique odio congue in. Phasellus imperdiet malesuada sem vel elementum. Pellentesque eu arcu in metus molestie cursus non ac lectus. Curabitur ut luctus orci, a rhoncus nisi. In luctus laoreet dui, vel ornare mauris ullamcorper vitae. Morbi dapibus faucibus orci, quis tempor lacus facilisis ac. Nam dignissim, leo at ultrices dignissim, ipsum dui consequat felis, a tincidunt odio odio ac libero.';
    $course_status = 'private';
    $course_hero_video_url = 'https://www.youtube.com/watch?v=y_bIr1yAELw&list=UULgqhMisF-ykzHZzuMEfV4Q';
    $course_class_size = 0; // zero means unlimited
    $course_who_can_enroll = 'anyone';
    $course_language = 'English';
    $course_open_ended = 'on';
    $course_open_ended_enrollment = 'on';	
    $course_allow_discussion = 'on';
    $course_show_grade_page = 'off';

    //If there isn't any course, create one
    
    $_courses_count = ( wp_count_posts( 'course' )->publish ) + ( wp_count_posts( 'course' )->private );
    
    if ( $_courses_count == 0 ) {
        $new_course = array(
            'post_author' => $course_author,
            'post_excerpt' => cp_filter_content($course_excerpt),
            'post_content' => cp_filter_content($course_content),
            'post_status' => $course_status,
            'post_title' => cp_filter_content($course_title, true),
            'post_type' => 'course',
        );

        $course_id = wp_insert_post( $new_course );
        
        if ( $course_id != 0 ) {
	        // $global_option = ! is_multisite();
            //Set a instructor - for the example, instructor will be the author of the post
            //update_user_option( $course_author, 'course_' . $course_id, $course_id, $global_option ); //only could be a user with "Intructor" role so admin can't be assigned

            //Set hero video
            update_post_meta( $course_id, 'course_video_url', cp_filter_content($course_hero_video_url, true) );

            //Set course class size
            update_post_meta( $course_id, 'class_size', cp_filter_content($course_class_size) );

            //Set who can enroll to the course
            update_post_meta( $course_id, 'enroll_type', cp_filter_content($course_who_can_enroll, true) );

            //Set course language
            update_post_meta( $course_id, 'course_language', cp_filter_content($course_language, true) );

            //Course is open-ended? Start date, but no end date.
            update_post_meta( $course_id, 'open_ended_course', cp_filter_content($course_open_ended) );
			
			//Students can enroll anytime?
			update_post_meta( $course_id, 'open_ended_enrollment', cp_filter_content($course_open_ended_enrollment) );

            //Allow course discussion?
            update_post_meta( $course_id, 'allow_course_discussion', cp_filter_content($course_allow_discussion) );

            //Show grade page?
            update_post_meta( $course_id, 'allow_course_grades_page', cp_filter_content($course_show_grade_page) );

            /*
             *
             * Insert first unit
             *  
             */

            $unit_title = 'Sed egestas erat nec purus';
            $unit_content = 'Praesent interdum nulla quis augue accumsan, vel pulvinar ligula cursus. Curabitur id nisi nisl. Proin aliquam lorem id urna molestie, non eleifend diam interdum.';
            $unit_status = 'publish';

            $new_unit = array(
                'post_author' => $course_author,
                'post_content' => cp_filter_content($unit_content),
                'post_status' => $unit_status, //$post_status
                'post_title' => cp_filter_content($unit_title, true),
                'post_type' => 'unit',
            );

            $unit_id = wp_insert_post( $new_unit );

            if ( $unit_id != 0 ) {

                $first_unit_id = $unit_id;

                //Set parent course
                update_post_meta( $unit_id, 'course_id', $course_id );
                
                //Set unit order to it's ID for start
                update_post_meta( $unit_id, 'unit_order', $unit_id );

                //To force or not to force the unit completion in order to access to the next one
                update_post_meta( $unit_id, 'force_current_unit_completion', 'off' );

                //Date from when the unit is available / set current date for the example
                update_post_meta( $unit_id, 'unit_availability', date( 'Y-m-d', current_time( 'timestamp', 0 ) ) );
            }

            /*
             *
             * Insert second unit
             *  
             */

            $unit_title = 'Pellentesque eu arcu in metus';
            $unit_content = 'Vestibulum tincidunt laoreet ultricies. Quisque quis diam orci. Pellentesque hendrerit, lectus sit amet pulvinar tristique, lorem lorem suscipit sapien, at sollicitudin augue nulla quis risus. Vestibulum vulputate sagittis risus id gravida.';
            $unit_status = 'publish';

            $new_unit = array(
                'post_author' => $course_author,
                'post_content' => cp_filter_content($unit_content),
                'post_status' => $unit_status, //$post_status
                'post_title' => cp_filter_content($unit_title, true),
                'post_type' => 'unit',
            );

            $unit_id = wp_insert_post( $new_unit );

            if ( $unit_id != 0 ) {

                $second_unit_id = $unit_id;

                //Set parent course
                update_post_meta( $unit_id, 'course_id', $course_id );
                
                //Set unit order to it's ID for start
                update_post_meta( $unit_id, 'unit_order', $unit_id );

                //To force or not to force the unit completion in order to access to the next one
                update_post_meta( $unit_id, 'force_current_unit_completion', 'off' );

                //Date from when the unit is available / set current date for the example
                update_post_meta( $unit_id, 'unit_availability', date( 'Y-m-d', current_time( 'timestamp', 0 ) ) );
            }

            /*
             *
             * Insert third unit
             *  
             */

            $unit_title = 'Donec rutrum tempor rutrum';
            $unit_content = 'Sed id dui orci. Integer pulvinar, nibh id aliquam feugiat, nibh dolor tempor erat, et commodo leo quam ut neque. Donec vulputate justo vel augue tristique, eu luctus magna fringilla. Morbi imperdiet blandit tempor. Nullam ut feugiat tortor. In diam eros, pulvinar nec malesuada vel, cursus sit amet augue. Sed posuere condimentum convallis.';
            $unit_status = 'publish';

            $new_unit = array(
                'post_author' => $course_author,
                'post_content' => cp_filter_content($unit_content),
                'post_status' => $unit_status, //$post_status
                'post_title' => cp_filter_content($unit_title, true),
                'post_type' => 'unit',
            );

            $unit_id = wp_insert_post( $new_unit );

            if ( $unit_id != 0 ) {

                $third_unit_id = $unit_id;

                //Set parent course
                update_post_meta( $unit_id, 'course_id', $course_id );
                
                //Set unit order to it's ID for start
                update_post_meta( $unit_id, 'unit_order', $unit_id );

                //To force or not to force the unit completion in order to access to the next one
                update_post_meta( $unit_id, 'force_current_unit_completion', 'off' );

                //Date from when the unit is available / set current date for the example
                update_post_meta( $unit_id, 'unit_availability', date( 'Y-m-d', current_time( 'timestamp', 0 ) ) );
            }


            /*
             *
             * Insert elements for the first unit
             *  
             */


            $unit_element = new Unit_Module();

            //Add an audio element

            $data->unit_id = $first_unit_id;
            $data->title = 'Audio Element Title';
            $data->content = 'Vivamus vulputate, ligula a tempus tempor, orci nulla interdum tortor, et malesuada augue nisi eget lacus.';
            $data->metas['audio_url'] = 'http://freemusicarchive.org/music/download/6a9cee592a19d5670fdc9db350bd4796d6c3fabf';
            $data->metas['autoplay'] = 'No';
            $data->metas['loop'] = 'No';
            $data->metas['show_title_on_front'] = 'yes';
            $data->metas['module_type'] = 'audio_module';
            $data->metas = array();
            $data->metas['module_order'] = 1;

            $unit_element->update_module( $data );

            //Add a video element

            $data->unit_id = $first_unit_id;
            $data->title = 'Video Element Title';
            $data->content = 'Vivamus vulputate, ligula a tempus tempor, orci nulla interdum tortor, et malesuada augue nisi eget lacus.';
            $data->metas['video_url'] = 'https://www.youtube.com/watch?v=O4brSJQX2EY&list=UULgqhMisF-ykzHZzuMEfV4Q';
            $data->metas['player_width'] = '960';
            $data->metas['show_title_on_front'] = 'yes';
            $data->metas['module_type'] = 'video_module';
            $data->metas['module_order'] = 2;

            $unit_element->update_module( $data );

            $data->unit_id = $first_unit_id;
            $data->title = 'Text Input Block Title';
            $data->content = 'Pellentesque gravida quam ac consectetur?';
            $data->metas['show_title_on_front'] = 'no';
            $data->metas['module_type'] = 'text_input_module';
            $data->metas['module_order'] = 3;

            $unit_element->update_module( $data );

            //Add a text element

            $data->unit_id = $first_unit_id;
            $data->title = 'Simple Text Block';
            $data->content = 'In sodales quam, vel vehicula lacus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Etiam bibendum viverra leo ut vestibulum.';
            $data->metas['show_title_on_front'] = 'yes';
            $data->metas['module_type'] = 'text_module';
            $data->metas['module_order'] = 4;

            $unit_element->update_module( $data );

            /*
             *
             * Insert elements for the second unit
             *  
             */

            //Add a video element

            $data->unit_id = $second_unit_id;
            $data->title = 'Video Element Title';
            $data->content = 'Vivamus vulputate, ligula a tempus tempor, orci nulla interdum tortor, et malesuada augue nisi eget lacus.';
            $data->metas['video_url'] = 'https://www.youtube.com/watch?v=XA0Apzy0V6M';
            $data->metas['player_width'] = '960';
            $data->metas['show_title_on_front'] = 'yes';
            $data->metas['module_type'] = 'video_module';
            $data->metas['module_order'] = 1;

            $unit_element->update_module( $data );

            //Add a text element

            $data->unit_id = $second_unit_id;
            $data->title = 'Text Element Title';
            $data->content = 'Cras ultricies molestie arcu, a consequat augue elementum molestie. Pellentesque gravida quam ac consectetur fermentum. Nunc accumsan felis risus, sit amet rutrum elit facilisis et. Fusce tempus interdum arcu, sed ornare tellus auctor vitae. Vivamus porta leo augue, sit amet aliquet turpis hendrerit pretium.

Donec sit amet erat eros. Mauris volutpat massa libero, pretium fermentum magna tempor quis. Nulla vel felis ligula. Sed dui velit, tristique at justo id, convallis rhoncus nisl. Aenean ut metus dictum, iaculis lacus nec, tincidunt ligula. Vivamus dictum odio sagittis sem consectetur porta. Nunc semper arcu ac arcu cursus, eget porta velit adipiscing. Suspendisse mattis neque in ipsum adipiscing, in convallis nulla laoreet. Quisque ac malesuada nunc, molestie dignissim sem.';
            $data->metas['show_title_on_front'] = 'yes';
            $data->metas['module_type'] = 'text_module';
            $data->metas['module_order'] = 2;

            $unit_element->update_module( $data );

            $data->unit_id = $second_unit_id;
            $data->title = 'Text Input Element Title';
            $data->content = 'Nullam quis pharetra tellus. Ut pretium metus pulvinar ligula scelerisque, ut rhoncus nibh laoreet. Aenean tincidunt iaculis facilisis. In cursus eu augue at pellentesque. Donec sed volutpat urna. Curabitur eu scelerisque nisi, feugiat gravida tellus. Maecenas sit amet quam et nisi hendrerit luctus sed in purus. Curabitur luctus ante lacus, ut laoreet tortor tristique quis. Integer id feugiat enim. Nulla a convallis mauris.';
            $data->metas['show_title_on_front'] = 'yes';
            $data->metas['module_type'] = 'textarea_input_module';
            $data->metas['module_order'] = 3;

            $unit_element->update_module( $data );


            /*
             *
             * Insert elements for the third unit
             *  
             */

            //Add a text element

            $data->unit_id = $third_unit_id;
            $data->title = 'Text Element Suspendisse mattis neque in ipsum adipiscing';
            $data->content = 'Vivamus vulputate, ligula a tempus tempor, orci nulla interdum tortor, et malesuada augue nisi eget lacus. Donec quis dapibus lorem. In hac habitasse platea dictumst.';
            $data->metas['show_title_on_front'] = 'yes';
            $data->metas['module_type'] = 'text_module';
            $data->metas['module_order'] = 2;

            $unit_element->update_module( $data );
            
            //Add a checkbox element

            $data->unit_id = $third_unit_id;
            $data->title = 'Multiple correct answers';
            $data->content = 'Praesent interdum ipsum eros, sit amet aliquet diam?';
            $data->metas['show_title_on_front'] = 'yes';
            $data->metas['module_type'] = 'checkbox_input_module';
            $data->metas['module_order'] = 3;
            $data->metas['answers'] = array( 'Fermentum ', 'Vivamus', 'Mauris' );
            $data->metas['checked_answers'] = array( 'Fermentum ', 'Vivamus' );

            $unit_element->update_module( $data );
            
            //Add a page break

            $data->unit_id = $third_unit_id;
            $data->title = '';
            $data->content = '';
            $data->metas['module_type'] = 'page_break_module';
            $data->metas['module_order'] = 4;

            $unit_element->update_module( $data );
            
            //Add a radio element
            
            $data->unit_id = $third_unit_id;
            $data->title = 'FREE Flat Social Icons';
            $data->content = 'Sed vulputate elit sed ligula bibendum blandit. Praesent quis mattis urna. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.';
            $data->metas['show_title_on_front'] = 'yes';
            $data->metas['module_type'] = 'file_module';
            $data->metas['module_order'] = 6;
            $data->metas['link_text'] = 'Donec bibendum auctor tellus et venenatis';
            $data->metas['file_url'] = 'https://github.com/danleech/simple-icons/archive/master.zip';

            $unit_element->update_module( $data );
            
            //Add a file input element

            $data->unit_id = $third_unit_id;
            $data->title = 'File Input Element';
            $data->content = 'Vivamus vulputate, ligula a tempus tempor, orci nulla interdum tortor, et malesuada augue nisi eget lacus. Donec quis dapibus lorem. In hac habitasse platea dictumst.';
            $data->metas['show_title_on_front'] = 'yes';
            $data->metas['module_type'] = 'file_input_module';
            $data->metas['module_order'] = 7;

            $unit_element->update_module( $data );
        }
    }
	update_option('cp_first_install', true);
}
?>