<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    use GlobalStatus;

    protected $casts = [
        'shortcode' => 'object',
    ];

    protected $hidden = ['script','shortcode'];

    public function generateScript()
    {
        if ($this->act == 'chatwoot') {
            $baseUrl = data_get($this->shortcode, 'base_url.value') ?: @$this->shortcode->base_url->value ?: @$this->shortcode['base_url']['value'] ?: 'https://app.chatwoot.com';
            $websiteToken = data_get($this->shortcode, 'website_token.value') ?: @$this->shortcode->website_token->value ?: @$this->shortcode['website_token']['value'];

            if ($websiteToken) {
                return '<script>
  window.chatwootSettings = {
    position: "right",
    type: "standard",
    launcherTitle: "",
    hideMessageBubble: true,
    showPopoutButton: true
  };
  (function(d,t) {
    var BASE_URL="' . $baseUrl . '";
    var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
    g.src=BASE_URL+"/packs/js/sdk.js";
    g.async = true;
    s.parentNode.insertBefore(g,s);
    g.onload=function(){
      window.chatwootSDK.run({
        websiteToken: "' . $websiteToken . '",
        baseUrl: BASE_URL
      });
    }
  })(document,"script");

  window.addEventListener("chatwoot:ready", function () {
    if (window.vinanceUser && window.vinanceUser.id) {
      window.$chatwoot.setUser(window.vinanceUser.id.toString(), {
        name: window.vinanceUser.name,
        email: window.vinanceUser.email,
        phone_number: window.vinanceUser.mobile || ""
      });
      window.$chatwoot.setCustomAttributes({
        user_id: window.vinanceUser.id,
        username: window.vinanceUser.username,
        platform: "Vinance Pro"
      });
    }
  });
</script>';
            }
        }

        $script = $this->script;
        if ($this->shortcode) {
            foreach ($this->shortcode as $key => $item) {
                $val = is_object($item) ? @$item->value : (is_array($item) ? @$item['value'] : (string)$item);
                $script = str_replace('{{' . $key . '}}', $val, $script);
            }
        }
        return $script;
    }

    public function scopeGenerateScript($query)
    {
        return $this->generateScript();
    }
}
