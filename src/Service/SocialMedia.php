<?php


namespace App\Service;


class SocialMedia {
    # All Social Media Sites
    # -------------------------------------------------

    # All Social Media Sites ~ Nice Names
    # -------------------------------------------------

    public function GetSocialMediaSites_NiceNames() {
        return [
            'blogger'=>'Blogger',
            'diaspora'=>'Diaspora',
            'douban'=>'Douban',
            'email'=>'EMail',
            'evernote'=>'EverNote',
            'getpocket'=>'Pocket',
            'facebook'=>'FaceBook',
            'flipboard'=>'FlipBoard',
            'google.bookmarks'=>'GoogleBookmarks',
            'instapaper'=>'InstaPaper',
            'line.me'=>'Line.me',
            'linkedin'=>'LinkedIn',
            'livejournal'=>'LiveJournal',
            'gmail'=>'GMail',
            'hacker.news'=>'HackerNews',
            'ok.ru'=>'OK.ru',
            'pinterest.com'=>'Pinterest',
            'qzone'=>'QZone',
            'reddit'=>'Reddit',
            'renren'=>'RenRen',
            'skype'=>'Skype',
            'sms'=>'SMS',
            'telegram.me'=>'Telegram.me',
            'threema'=>'Threema',
            'tumblr'=>'Tumblr',
            'twitter'=>'Twitter',
            'vk'=>'VK',
            'weibo'=>'Weibo',
            'whatsapp'=>'WhatsApp',
            'xing'=>'Xing',
            'yahoo'=>'Yahoo',
        ];
    }

    # Social Media Sites With Share Links
    # -------------------------------------------------

    public function GetSocialMediaSites_WithShareLinks_OrderedByPopularity() {
        return [
            'google.bookmarks',
            'facebook',
            'reddit',
            'whatsapp',
            'twitter',
            'linkedin',
            'tumblr',
            'pinterest',
            'blogger',
            'livejournal',
            'evernote',
            'getpocket',
            'hacker.news',
            'flipboard',
            'instapaper',
            'diaspora',
            'qzone',
            'vk',
            'weibo',
            'ok.ru',
            'douban',
            'xing',
            'renren',
            'threema',
            'sms',
            'line.me',
            'skype',
            'telegram.me',
            'email',
            'gmail',
            'yahoo',
        ];
    }

    public function GetSocialMediaSites_WithShareLinks_OrderedByAlphabet() {
        $nice_names = $this->GetSocialMediaSites_NiceNames();

        return array_keys($nice_names);
    }

    # Social Media Site Links With Share Links
    # -------------------------------------------------

    public function GetSocialMediaSiteLinks_WithShareLinks($args) {
        $url = urlencode($args['url']);
        $title = urlencode($args['title']);



        $text = $title;



        // conditional check before arg appending

        return [
            'evernote'=>'https://www.evernote.com/clip.action?url=' . urldecode($url) . '&title=' . $text,
            'facebook'=>'http://www.facebook.com/sharer.php?u=' . urldecode($url),
            'linkedin'=>'https://www.linkedin.com/sharing/share-offsite/?url=' . urldecode($url),
            'skype'=>'https://web.skype.com/share?url=' . urldecode($url) . '&text=' . $text,
            'whatsapp'=>'https://api.whatsapp.com/send?text=' . $text . '%20' . urldecode($url),
        ];
    }
}
