<template>
    <div ref="chat_content" v-html="contentMessage" class="whitespace-pre-wrap"></div>
</template>

<script>

import Constant from '@/utility/constant'
import { uuidv4 } from '@/utility/index'

export default {
    props: ['content'],
    data(){
        return{
            contentMessage: this.renderChatContent()
        }
    },
    mounted () {
        this.bindEventHtml()
    },
    methods: {
        extractMentions(string, mentionChar) {
            if(mentionChar){
                const allMatches = string.match(mentionChar) || [];
                const validMentions = allMatches.filter(mention => {
                    const cleanedMention = mention.slice(1);
                    return /^[\w_]+$/.test(cleanedMention);
                });
                return validMentions;
            }
            return []
        },
        renderChatContent(){
            var result = this.content
            var self = this
    
            if (result) {

                //link 
                var links = result.match(Constant.LINK_REGEX)
                var tmp_links = [];
                if (links && links.length > 0)  {
                    links.map((link, key) => {
                        key = uuidv4()
                        tmp_links[key] = link.replace(link, '<a class="external-link underline text-inherit " target="_blank" href="' + link + '">' + link + '</a>')
                        result = result.replace(link, key)
                    });                    
                }            
                
                //hashtag
                var hashtags = this.extractMentions(result, Constant.HASHTAG_REGEX)
                var tmp_hashtags = [];
                if (hashtags && hashtags.length > 0) {
                    hashtags.map((hashtag ,key) => {
                        var hashtagUrl = self.$router.resolve({
                            name: 'search',
                            params: {'search_type': 'hashtag', 'type' : 'post'},
                            query: {
                                q: hashtag.replace('#', '')
                            }
                        })
                        key = uuidv4()
                        tmp_hashtags[key] = hashtag.replace(hashtag, '<a class="text-inherit internal-link underline" data-internal_href="'+hashtagUrl.fullPath+'" href="' + hashtagUrl.href + '">' + hashtag + '</a>')
                        result = result.replace(hashtag, key)
                    });
                }

                // mention
                var mentions = this.extractMentions(result, Constant.MENTION_REGEX)
                var tmp_mentions = [];
                if (mentions && mentions.length > 0) {
                    mentions.map((mention ,key) => {
                        var profileUrl = self.$router.resolve({
                            name: 'profile',
                            params: {user_name: mention.replace('@', '')}                       
                        })
                        key = uuidv4()
                        tmp_mentions[key] = mention.replace(mention, '<a class="text-inherit internal-link underline" data-internal_href="'+profileUrl.fullPath+'" href="' + profileUrl.href + '">' + mention + '</a>')
                        result = result.replace(mention, key)
                    });
                }
            }

            window._.forIn(tmp_links, function(tmp_link, key) {
                result = result.replace(key, tmp_link)
            });
            window._.forIn(tmp_hashtags, function(tmp_link, key) {
                result = result.replace(key, tmp_link)
            });
            window._.forIn(tmp_mentions, function(tmp_hashtag, key) {
                result = result.replace(key, tmp_hashtag)
            });
            return result
        },
        bindEventHtml() {
            var links = this.$refs.chat_content.querySelectorAll('a')
            for (let i = 0; i < links.length; i++) {
                links[i].addEventListener('click', (event) => {
                    var target = event.target
                    var link = target.getAttribute('data-internal_href')
                    if (target.classList.contains('external-link')) {
                        return
                    } else {
                        this.$router.push(link)
                    }
                    event.preventDefault()
                })
            }
        }
    }
}
</script>