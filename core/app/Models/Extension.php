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

    public function scopeGenerateScript()
    {
        if ($this->act == 'chatwoot') {
            $baseUrl = @$this->shortcode->base_url->value ?: 'https://app.chatwoot.com';
            $websiteToken = @$this->shortcode->website_token->value;
            if (!$websiteToken) {
                return '';
            }
            return '<script>
  window.chatwootSettings = {"position":"right","type":"standard","launcherTitle":""};
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

        $script = $this->script;
        foreach ($this->shortcode as $key => $item) {
            $script = str_replace('{{' . $key . '}}', $item->value, $script);
        }
        return $script;
    }
}
