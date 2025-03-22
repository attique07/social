<header class="modal-card-head">
    <p class="modal-card-title">{{ $title }}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="subscription_package_form" method="post" enctype="multipart/form-data" action="{{ route('admin.user_subscription.store')}}">
            <div id="errors"></div>
            <input type="hidden" name="id" value="{{ $package->id }}" class="form-control"/>
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" value="{{$package->name }}" type="text">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Description')}}</label>
                <textarea id="description" name="description" class="form-control" @if ($package->id) readonly @endif >{{$package->getTranslatedAttributeValue('description') }}</textarea>
                @if ($package->id)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$package->getTable(),'description',$package->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Role')}}</label>            
                <select name="role_id" class="form-select">
                    @foreach($roles as $role)
                        <option @if ((! $package->role_id && $role->is_default) || $package->role_id == $role->id) selected @endif value="{{$role->id}}">{{$role->name}}</option>
                    @endforeach
                </select>    
                <p class="help">
                    {{__('The member will be upgraded to this level upon subscribing to this plan.')}}
                </p>         
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Downgrade user role')}}</label>            
                <select name="expire_role_id" class="form-select">
                    @foreach($roles as $role)
                        <option @if ((! $package->expire_role_id && $role->is_default) || $package->expire_role_id == $role->id) selected @endif value="{{$role->id}}">{{$role->name}}</option>
                    @endforeach
                </select>    
                <p class="help">
                    {{__('The role that user will be auto assigned upon membership downgrade.')}}
                </p>         
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>        
                <div class="form-check form-switch">
                    <input name="is_active" class="form-check-input" type="checkbox" {{ $package->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="subscription_package_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script src="{{ asset('admin/js/translation.js') }}"></script>
<script src="{{ asset('admin/js/user_subscription.js') }}"></script>
<script>
    adminUserSubscription.initCreatePackage();
</script>