<style>
/* Notification dropdown styles */
.hh-notify { position: relative; display: inline-block; }
.hh-notify .icon { position: relative; cursor: pointer; padding:8px; border-radius:8px; }
.hh-notify .badge { position: absolute; top:2px; right:2px; background:#E53E3E; color:#fff; font-size:12px; padding:2px 6px; border-radius:12px; }
.hh-notify .dropdown { display:none; position:absolute; right:0; top:36px; min-width:320px; background:#fff; box-shadow:0 6px 18px rgba(0,0,0,.12); border-radius:12px; overflow:hidden; z-index:12000 }
.hh-notify .dropdown.show { display:block; }
.hh-notify .dropdown .head { padding:12px 14px; border-bottom:1px solid #eee; font-weight:700; display:flex; align-items:center; justify-content:space-between; }
.hh-notify .dropdown .head .close { cursor:pointer; background:transparent;border:0;font-size:18px;color:#666;padding:4px 8px;border-radius:6px }
.hh-notify .dropdown .head .close:hover { background:#f3f3f3 }
.hh-notify .dropdown .list { max-height:320px; overflow:auto; }
.hh-notify .dropdown .item { display:flex; gap:10px; padding:10px 14px; border-bottom:1px solid #f3f3f3; }
.hh-notify .dropdown .item.unread { background:linear-gradient(90deg, rgba(233,236,255,.45), transparent); }
.hh-notify .dropdown .item .meta { font-size:12px; color:#666 }
.hh-notify .dropdown .item { color:#2b2b2b }
.hh-notify .dropdown .empty { padding:18px; color:#777; text-align:center }
.hh-notify .dropdown .actions { padding:10px; text-align:center }
.hh-notify .dropdown .actions button { background:#5A3E2B;color:#fff;border:none;padding:8px 12px;border-radius:8px }
</style>
