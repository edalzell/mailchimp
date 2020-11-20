<?php

namespace Silentz\Mailchimp;

use Edalzell\Forma\Forma;
use Silentz\Mailchimp\Fieldtypes\FormField;
use Silentz\Mailchimp\Fieldtypes\MailchimpAudience;
use Silentz\Mailchimp\Fieldtypes\MailchimpMergeFields;
use Silentz\Mailchimp\Fieldtypes\MailchimpTag;
use Silentz\Mailchimp\Listeners\AddFromSubmission;
use Statamic\Events\SubmissionCreated;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use Statamic\Support\Arr;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        MailchimpAudience::class,
        MailchimpTag::class,
        // FormField::class,
        MailchimpMergeFields::class,
    ];

    protected $listen = [
        // 'user.registered' => [
        //     'Edalzell\Mailchimp\AddFromUser',
        // ],
        SubmissionCreated::class => [AddFromSubmission::class],
    ];

    protected $scripts = [
        __DIR__.'/../dist/js/cp.js',
    ];

    public function boot()
    {
        parent::boot();
        Statamic::booted(function () {
            Forma::registerAddon('silentz/mailchimp');
        });

        $this->app->booted(fn () => $this->addFormsToNewsletterConfig());
    }

    private function addFormsToNewsletterConfig()
    {
        $lists = collect(config('mailchimp.forms'))
            ->flatMap(function ($form) {
                if (! $handle = Arr::get($form, 'form')) {
                    return [];
                }

                return [$handle => ['id' => Arr::get($form, 'audience_id')]];
            })
            ->all();

        config(['newsletter.lists' => $lists]);
    }
}
