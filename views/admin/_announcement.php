<div class="card mb-3  bg-info text-white">
    <form method="post" action="<?= route('announcement'); ?>">
        <div class="card-header p-3">Slack Announcement (<?= htmlspecialchars(getenv('SLACK_CHANNEL')); ?>)</div>
        <div class="card-body p-0">
            <textarea id="message" name="message" class="form-control p-3 m-0 text-monospace"
                      placeholder="Message text..."
            ></textarea>
        </div>
        <div class="card-footer p-1">
            <button type="submit" class="btn btn-primary btn-block btn-sm">Send</button>
        </div>
    </form>
</div>
<script>
    autosize(document.getElementById('message'));
</script>
