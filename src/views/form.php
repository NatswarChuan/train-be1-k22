<form action="<?php route("/exe-array/bai-3") ?>" method="post">
    <label class="form-label">Name</label>
    <input name="name" type="text" class="form-control" />
    <label class="form-label">Email</label>
    <input name="email" type="text" class="form-control" />
    <label class="form-label">Website</label>
    <input name="website" type="text" class="form-control" />
    <label class="form-label">Comment</label>
    <textarea name="comment" type="text" class="form-control"></textarea>
    <label class="form-label">Gender</label>
    <div class="form-check">
        <input name="gender" type="radio" class="form-check-input" id="formCheck-1" value="male" />
        <label class="form-check-label" for="formCheck-1">Male</label>
    </div>
    <div class="form-check">
        <input name="gender" type="radio" class="form-check-input" id="formCheck-2" value="female" />
        <label class="form-check-label" for="formCheck-2">Female</label>
    </div>
    <button type="submit" class="btn btn-primary">send</button>
</form>