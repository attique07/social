<template>
  <span class="base-icon inline-block align-middle">
    <component
      :is="name"
      :size="size"
      :class="svgClass">
    </component>
  </span>
</template>
    
<script>
import emoji from './emoji.vue'
import close from './close.vue'
import plus from './plus.vue'
import message from './message.vue'
import notification from './notification.vue'
import new_post from './new_post.vue'
import new_post_mobile from './new_post_mobile.vue'
import search from './search.vue'
import home from './home.vue'
import profile from './profile.vue'
import lists from './lists.vue'
import discovery from './discovery.vue'
import bookmarks from './bookmarks.vue'
import settings from './settings.vue'
import invite from './invite.vue'
import globe from './globe.vue'
import logout from './logout.vue'
import like from './like.vue'
import comment from './comment.vue'
import share from './share.vue'
import more_horiz_outlined from './more_horiz_outlined.vue'
import like_active from './like_active.vue'
import bookmark_active from './bookmark_active.vue'
import hashtag from './hashtag.vue'
import circle_check from './circle_check.vue'
import hamburger from './hamburger.vue'
import pencil from './pencil.vue'
import big_hashtag from './big_hashtag.vue'
import copy_link from './copy_link.vue'
import import_icon from './import_icon.vue'
import more_horizontal from './more_horizontal.vue'
import arrow_right from './arrow_right.vue'
import circle_check_outlined from './circle_check_outlined.vue'
import big_more from './big_more.vue'
import people from './people.vue'
import more_circle from './more_circle.vue'
import arrow_left from './arrow_left.vue'
import error from './error.vue'
import success from './success.vue'
import camera from './camera.vue'
import check from './check.vue'
import mute_sm from './mute_sm.vue'
import send_message from './send_message.vue'
import linkedin from './linkedin.vue'
import pinterest from './pinterest.vue'
import facebook from './facebook.vue'
import twitter from './twitter.vue'
import caret_down from './caret_down.vue'
import caret_left from './caret_left.vue'
import caret_right from './caret_right.vue'
import key from './key.vue'
import apple from './apple.vue'
import google from './google.vue'
import facebook_small from './facebook_small.vue'
import more_vertical from './more_vertical.vue'
import eye from './eye.vue'
import eye_slash from './eye_slash.vue'
import feeds from './feeds.vue'
import info from './info.vue'
import video_camera from './video_camera.vue'
import light from './light.vue'
import dark from './dark.vue'
import character from './character.vue'
import photo from './photo.vue'
import play from './play.vue'
import pause from './pause.vue'
import mute from './mute.vue'
import unmute from './unmute.vue'
import music from './music.vue'
import play_song from './play_song.vue'
import pause_song from './pause_song.vue'
import music_note from './music_note.vue'
import music_notes from './music_notes.vue'
import background from './background.vue'
import fullscreen from './fullscreen.vue'
import fullscreen_exit from './fullscreen_exit.vue'
import desktop from './desktop.vue'
import upload_data from './upload_data.vue'
import arrow_downward from './arrow_downward.vue'
import ios from './ios.vue'
import google_play from './google_play.vue'
import big_close from './big_close.vue'
import chevron_left from './chevron_left.vue'
import chevron_right from './chevron_right.vue'
import spinner from './spinner.vue'
import images from './images.vue'
import video_solid from './video_solid.vue'
import page_switch from './page_switch.vue'
import phone from './phone.vue'
import circle_switch from './circle_switch.vue'
import clock from './clock.vue'
import coin from './coin.vue'
import earth from './earth.vue'
import mail from './mail.vue'
import star from './star.vue'
import lock from './lock.vue'
import friend from './friend.vue'
import location from './location.vue'
import media from './media.vue'
import users from './users.vue'
import trash from './trash.vue'
import file from './file.vue'
import briefcase from './briefcase.vue'
import youtube from './youtube.vue'
import paperclip from './paperclip.vue'
import money from './money.vue'
import check_circle from './check_circle.vue'
import chats from './chats.vue'
import gif from './gif.vue'
import x_circle from './x_circle.vue'

export default {
  name: 'BaseIcon',
  props: {
    name: {
      type: String,
      default: ''
    },
    size: {
      type: String,
      default: '24'
    },
    color: {
      type: String,
      default: 'currentColor'
    },
    svgClass: {
      type: String,
      default: ''
    }
  },

  components: {
    emoji,
    close,
    plus,
    message,
    notification,
    new_post,
    new_post_mobile,
    search,
    home,
    profile,
    lists,
    discovery,
    bookmarks,
    settings,
    invite,
    globe,
    logout,
    like,
    comment,
    share,
    more_horiz_outlined,
    like_active,
    bookmark_active,
    hashtag,
    circle_check,
    circle_check_outlined,
    hamburger,
    pencil,
    big_hashtag,
    copy_link,
    import_icon,
    more_horizontal,
    more_vertical,
    key,
    arrow_right,
    big_more,
    people,
    more_circle,
    arrow_left,
    error,
    success,
    camera,
    check,
    mute_sm,
    send_message,
    linkedin,
    pinterest,
    twitter,
    caret_down,
    caret_left,
    caret_right,
    apple,
    google,
    facebook,
    facebook_small,
    eye,
    eye_slash,
    feeds,
    info,
    video_camera,
    light,
    dark,
    character,
    photo,
    play,
    pause,
    mute,
    unmute,
    music,
    play_song,
    pause_song,
    music_note,
    music_notes,
    background,
    fullscreen,
    fullscreen_exit,
    desktop,
    upload_data,
    arrow_downward,
    ios,
    google_play,
    big_close,
    chevron_left,
    chevron_right,
    spinner,
    images,
    video_solid,
    page_switch,
    phone,
    circle_switch,
    clock,
    coin,
    earth,
    mail,
    star,
    lock,
    friend,
    location,
    media,
    users,
    trash,
    file,
    briefcase,
    youtube,
    paperclip,
    money,
    check_circle,
    chats,
    gif,
    x_circle
  }
}
</script>