<div class="admin-card has-form-group" id="{{$group->key . $groupSub->id}}">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__($groupSub->name)}}</h5>
        @includeIf($groupSub->package.'::admin.partial.setting.group_sub_title_'.$groupSub->key)
    </div>
    <div class="admin-card-body">
        @include('shaun_core::admin.partial.setting.fields',['settings' => $groupSub->settings])
        @includeIf($groupSub->package.'::admin.partial.setting.group_sub.'.$groupSub->key,['settings' => $groupSub->settings])
    </div>
</div>