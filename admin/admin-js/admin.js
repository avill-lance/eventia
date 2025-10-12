    (() => {
    const $ = sel => document.querySelector(sel)
    const $$ = sel => Array.from(document.querySelectorAll(sel))
    const editor = $('#editorModal')

    // -------------------- STORAGE --------------------
    const DB_KEYS = {
      packages:'db_packages', services:'db_services', blog:'db_blog', bookings:'db_bookings', products:'db_products',
      inquiries:'db_inquiries', reviews:'db_reviews', settings:'db_settings'
    }
    const nowISO = () => new Date().toISOString()
    const uid = () => Math.random().toString(36).slice(2,9)

    const defaults = {
      [DB_KEYS.settings]: {
        adminName:'Admin', pageName:'My Business', about:'We craft awesome things.', privacy:'Your data is safe with us.'
      },
      [DB_KEYS.packages]: [
        {id:uid(), name:'Starter Pack', desc:'Good for small teams', status:'live', createdAt:nowISO()},
        {id:uid(), name:'Pro Pack', desc:'For growing orgs', status:'draft', createdAt:nowISO()},
      ],
      [DB_KEYS.services]: [
        {id:uid(), name:'Consulting', desc:'Hourly guidance', status:'live', createdAt:nowISO()},
      ],
      [DB_KEYS.blog]: [
        {id:uid(), name:'Welcome to our blog', desc:'First post', status:'live', createdAt:nowISO()},
      ],
      [DB_KEYS.bookings]: [
        {id:uid(), name:'John Deo', desc:'Booked Pro Pack - 2025-09-03', status:'live', createdAt:nowISO()},
      ],
      [DB_KEYS.products]: [
        {id:uid(), name:'Sticker Pack', desc:'Vinyl stickers', status:'live', createdAt:nowISO()},
      ],
      [DB_KEYS.inquiries]: [
        {id:uid(), name:'jane@example.com', desc:'Do you ship internationally?', status:'unread', createdAt:nowISO()},
      ],
      [DB_KEYS.reviews]: [
        {id:uid(), name:'Alex', desc:'Great service!', status:'live', createdAt:nowISO()},
      ],
    }

    const db = {
      get(key){
        const raw = localStorage.getItem(key)
        if(!raw){
          localStorage.setItem(key, JSON.stringify(defaults[key] ?? []))
          return structuredClone(defaults[key] ?? [])
        }
        try { return JSON.parse(raw) } catch { return [] }
      },
      set(key,val){ localStorage.setItem(key, JSON.stringify(val)) },
      upsert(key,item){
        const list = db.get(key)
        const idx = list.findIndex(x=>x.id===item.id)
        if(idx>-1) list[idx]=item; else list.unshift(item)
        db.set(key,list); return item
      },
      remove(key,id){ db.set(key, db.get(key).filter(x=>x.id!==id)) }
    }

    // -------------------- UTIL UI --------------------
    const toast = (msg) => {
      const t = $('#toast'); t.textContent = msg; t.style.display='block';
      setTimeout(()=>t.style.display='none', 1800)
    }

    const fmtDate = iso => new Date(iso).toLocaleString()

    const badge = (status) => `<span class="status ${status}">${status}</span>`

    const table = (rows, headers) => `
      <table class="table">
        <thead><tr>${headers.map(h=>`<th>${h}</th>`).join('')}</tr></thead>
        <tbody>${rows.join('')}</tbody>
      </table>`

    const actionsCell = (key, id, allowDelete=true) => `
      <div style="display:flex;gap:8px">
        <button class="btn" data-ed="${key}:${id}">Edit</button>
        ${allowDelete?`<button class="btn danger" data-del="${key}:${id}">Delete</button>`:''}
      </div>`

    // -------------------- GENERIC VIEW RENDERER --------------------
    const renderListView = ({key, mountId, title, allowDelete=true, extraCols=[]}) => {
      const mount = document.getElementById(mountId)
      const items = db.get(key)
      const search = ($('#globalSearch').value||'').toLowerCase()
      const filtered = items.filter(i => [i.name, i.desc, i.email].filter(Boolean).join(' ').toLowerCase().includes(search))

      const headers = ['Name/Title','Description','Status','Created','Actions']
      const rows = filtered.map(i => `
        <tr>
          <td>${i.name}</td>
          <td>${i.desc ?? ''}</td>
          <td>${badge(i.status)}</td>
          <td>${fmtDate(i.createdAt)}</td>
          <td>${actionsCell(key,i.id,allowDelete)}</td>
        </tr>`)

      mount.innerHTML = `
        <div class="card">
          <div style="display:flex;align-items:center;gap:8px;justify-content:space-between;margin-bottom:8px">
            <div>
              <h3 style="margin:0 0 6px 0">${title}</h3>
              <div class="muted">Total: ${items.length} • Showing: ${filtered.length}</div>
            </div>
            <div class="toolbar">
              <button class="btn primary" data-add="${key}">Add New</button>
              <button class="btn" data-markallread="${key}">Mark all as read</button>
              <button class="btn" data-export="${key}">Export</button>
              <label class="btn" for="import-${key}">Import</label>
              <input type="file" id="import-${key}" accept="application/json" hidden />
            </div>
          </div>
          ${table(rows, headers)}
        </div>
      `

      // wire buttons inside this mount
      mount.querySelectorAll('[data-add]').forEach(btn => btn.onclick = () => openEditor(key))
      mount.querySelectorAll('[data-ed]').forEach(btn => btn.onclick = () => {
        const [k,id] = btn.dataset.ed.split(':'); const item = db.get(k).find(x=>x.id===id); openEditor(k,item)
      })
      mount.querySelectorAll('[data-del]').forEach(btn => btn.onclick = () => {
        const [k,id] = btn.dataset.del.split(':')
        if(!allowDelete){ toast('Deletion disabled for this module.'); return }
        if(confirm('Delete this item?')){ db.remove(k,id); toast('Deleted.'); router.render() }
      })
      mount.querySelectorAll('[data-export]').forEach(btn => btn.onclick = () => exportKey(btn.dataset.export))
      const inp = mount.querySelector(`#import-${key}`)
      if(inp) inp.onchange = (e) => importKey(key, e.target.files?.[0])
      mount.querySelectorAll('[data-markallread]').forEach(btn => btn.onclick = () => { if(btn.dataset.markallread==='inquiries'){ markAllRead() }} )
    }

    // -------------------- ROUTER --------------------
    const router = {
      current:'dashboard',
      routes:['dashboard','packages','services','blog','bookings','products','inquiries','reviews','settings'],
      go(hash){
        const r = (hash||location.hash||'#/dashboard').replace('#/','')
        this.current = this.routes.includes(r)? r : 'dashboard'
        this.render()
      },
      render(){
        // nav
        $$('#nav a').forEach(a => a.classList.toggle('active', a.dataset.route===this.current))
        // views
        $$('.view').forEach(v => v.hidden = true)
        const id = `#view-${this.current}`
        $(id).hidden = false
        // specific renders
        if(this.current==='dashboard') drawDashboard()
        if(this.current==='packages') renderListView({key:DB_KEYS.packages, mountId:'view-packages', title:'Packages'})
        if(this.current==='services') renderListView({key:DB_KEYS.services, mountId:'view-services', title:'Services'})
        if(this.current==='blog') renderListView({key:DB_KEYS.blog, mountId:'view-blog', title:'Blog'})
        if(this.current==='bookings') renderListView({key:DB_KEYS.bookings, mountId:'view-bookings', title:'Package booking'})
        if(this.current==='products') renderListView({key:DB_KEYS.products, mountId:'view-products', title:'Shop manage product'})
        if(this.current==='inquiries') renderListView({key:DB_KEYS.inquiries, mountId:'view-inquiries', title:'Inquiries'})
        if(this.current==='reviews') renderListView({key:DB_KEYS.reviews, mountId:'view-reviews', title:'Clients review', allowDelete:true})
        if(this.current==='settings') drawSettings()
      }
    }

    window.addEventListener('hashchange', () => router.go(location.hash))

    // -------------------- DASHBOARD --------------------
    const drawDashboard = () => {
      const counts = {
        packages: db.get(DB_KEYS.packages).length,
        services: db.get(DB_KEYS.services).length,
        blog: db.get(DB_KEYS.blog).length,
        bookings: db.get(DB_KEYS.bookings).length,
        products: db.get(DB_KEYS.products).length,
        inquiries: db.get(DB_KEYS.inquiries).length,
        unread: db.get(DB_KEYS.inquiries).filter(x=>x.status==='unread').length,
        reviews: db.get(DB_KEYS.reviews).length,
      }
      const cards = [
        {label:'Packages', val:counts.packages, route:'packages'},
        {label:'Services', val:counts.services, route:'services'},
        {label:'Blog posts', val:counts.blog, route:'blog'},
        {label:'Bookings', val:counts.bookings, route:'bookings'},
        {label:'Products', val:counts.products, route:'products'},
        {label:'Inquiries', val:counts.inquiries, route:'inquiries'},
        {label:'Unread inquiries', val:counts.unread, route:'inquiries'},
        {label:'Reviews', val:counts.reviews, route:'reviews'},
      ]
      $('#statsCards').innerHTML = cards.map(c => `
        <a class="card" href="#/${c.route}">
          <h3>${c.label}</h3>
          <div class="big">${c.val}</div>
        </a>`).join('')

      const recent = [
        ...db.get(DB_KEYS.packages).slice(0,3).map(i=>({at:i.createdAt,text:`Package: ${i.name}`})),
        ...db.get(DB_KEYS.services).slice(0,3).map(i=>({at:i.createdAt,text:`Service: ${i.name}`})),
        ...db.get(DB_KEYS.blog).slice(0,3).map(i=>({at:i.createdAt,text:`Blog: ${i.name}`})),
        ...db.get(DB_KEYS.products).slice(0,3).map(i=>({at:i.createdAt,text:`Product: ${i.name}`})),
      ].sort((a,b)=> new Date(b.at)-new Date(a.at)).slice(0,8)
      $('#activity').innerHTML = recent.map(x=>`<div style="padding:8px 0;border-bottom:1px dashed ${getComputedStyle(document.documentElement).getPropertyValue('--border')}">
        <div>${x.text}</div><div class="muted" style="font-size:12px">${fmtDate(x.at)}</div></div>`).join('')
    }

    // Quick add
    $('#quickAdd').onclick = () => {
      const map = {packages:DB_KEYS.packages, services:DB_KEYS.services, blog:DB_KEYS.blog, products:DB_KEYS.products}
      const key = map[$('#quickType').value]
      if(!key) return
      const item = {id:uid(), name:$('#quickName').value||'Untitled', desc:$('#quickDesc').value||'', status:'draft', createdAt:nowISO()}
      db.upsert(key,item); toast('Added.'); $('#quickName').value=''; $('#quickDesc').value=''; router.render()
    }
    $('#quickClear').onclick = () => { $('#quickName').value=''; $('#quickDesc').value='' }

    // -------------------- EDITOR MODAL --------------------
    let editing = { key:null, id:null }
    const openEditor = (key,item=null) => {
      editing.key = key
      $('#editorTitle').textContent = item? 'Edit item' : 'Add item'
      $('#ed-name').value = item?.name || ''
      $('#ed-desc').value = item?.desc || ''
      $('#ed-status').value = item?.status || 'draft'
      $('#ed-extra').innerHTML = ''

      // field variations by module
      if(key===DB_KEYS.products){
        $('#ed-extra').innerHTML = `
          <div class="form" style="grid-template-columns:repeat(3,minmax(0,1fr))">
            <div class="field"><label>Price</label><input id="ed-price" type="number" step="0.01" value="${item?.price??''}"></div>
            <div class="field"><label>Stock</label><input id="ed-stock" type="number" value="${item?.stock??''}"></div>
            <div class="field"><label>SKU</label><input id="ed-sku" value="${item?.sku??''}"></div>
          </div>`
      }
      if(key===DB_KEYS.inquiries){
        $('#ed-extra').innerHTML = `
          <div class="form" style="grid-template-columns:repeat(2,minmax(0,1fr))">
            <div class="field"><label>Email</label><input id="ed-email" value="${item?.name??''}" placeholder="email@example.com"></div>
            <div class="field"><label>Mark as</label>
              <select id="ed-status-inq">
                <option value="unread" ${item?.status==='unread'?'selected':''}>Unread</option>
                <option value="live" ${item?.status==='live'?'selected':''}>Read</option>
              </select>
            </div>
          </div>`
      }
      if(key===DB_KEYS.reviews){
        $('#ed-extra').innerHTML = `
          <div class="form" style="grid-template-columns:repeat(2,minmax(0,1fr))">
            <div class="field"><label>User name</label><input id="ed-user" value="${item?.name??''}"></div>
            <div class="field"><label>Rating (1-5)</label><input id="ed-rating" type="number" min="1" max="5" value="${item?.rating??5}"></div>
          </div>`
      }

      editor.showModal()
      $('#ed-cancel').onclick = () => editor.close()
      $('#ed-save').onclick = () => {
        const list = db.get(key)
        let obj = item || { id: uid(), createdAt: nowISO() }
        obj.name = $('#ed-name').value || 'Untitled'
        obj.desc = $('#ed-desc').value || ''
        obj.status = (key===DB_KEYS.inquiries? ($('#ed-status-inq')?.value||'unread') : $('#ed-status').value)
        // extras
        if(key===DB_KEYS.products){ obj.price = Number($('#ed-price').value||0); obj.stock = Number($('#ed-stock').value||0); obj.sku = $('#ed-sku').value||'' }
        if(key===DB_KEYS.inquiries){ obj.email = $('#ed-email').value||''; obj.name = obj.email || obj.name }
        if(key===DB_KEYS.reviews){ obj.user = $('#ed-user').value||obj.name; obj.name = obj.user; obj.rating = Number($('#ed-rating').value||5) }
        db.upsert(key,obj); toast('Saved.'); editor.close(); router.render()
      }
    }

    // Mark all inquiries read
    const markAllRead = () => {
      const list = db.get(DB_KEYS.inquiries).map(i => ({...i, status:'live'}))
      db.set(DB_KEYS.inquiries,list); toast('All inquiries marked as read.'); router.render()
    }

    // Export/Import
    const exportKey = (key) => {
      const blob = new Blob([JSON.stringify(db.get(key),null,2)], {type:'application/json'})
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = `${key}.json`; a.click(); URL.revokeObjectURL(a.href)
    }
    const importKey = (key, file) => {
      if(!file) return
      const reader = new FileReader()
      reader.onload = () => { try{ const data = JSON.parse(reader.result); if(Array.isArray(data)){ db.set(key,data); toast('Imported.'); router.render() } else toast('Invalid file.') } catch { toast('Invalid JSON') } }
      reader.readAsText(file)
    }

    // -------------------- SETTINGS --------------------
    const drawSettings = () => {
      const s = db.get(DB_KEYS.settings)
      $('#set-adminName').value = s.adminName||''
      $('#set-pageName').value = s.pageName||''
      $('#set-about').value = s.about||''
      $('#set-privacy').value = s.privacy||''
    }
    $('#settingsSave').onclick = () => {
      const s = {
        adminName: $('#set-adminName').value,
        pageName: $('#set-pageName').value,
        about: $('#set-about').value,
        privacy: $('#set-privacy').value,
      }
      db.set(DB_KEYS.settings,s); toast('Settings saved.'); syncHeader()
    }
    $('#settingsExport').onclick = () => {
      const all = Object.fromEntries(Object.values(DB_KEYS).map(k => [k, db.get(k)]))
      const blob = new Blob([JSON.stringify(all,null,2)], {type:'application/json'})
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = `dashboard-backup.json`; a.click(); URL.revokeObjectURL(a.href)
    }
    $('#settingsImport').onchange = (e) => {
      const file = e.target.files?.[0]; if(!file) return
      const reader = new FileReader()
      reader.onload = () => { try{ const data = JSON.parse(reader.result); Object.entries(data).forEach(([k,v])=>{ if(DB_KEYS.packages===k||DB_KEYS.services===k||DB_KEYS.blog===k||DB_KEYS.bookings===k||DB_KEYS.products===k||DB_KEYS.inquiries===k||DB_KEYS.reviews===k||DB_KEYS.settings===k){ db.set(k,v) } }); toast('Backup restored.'); router.render(); syncHeader() } catch{ toast('Invalid backup JSON') } }
      reader.readAsText(file)
    }

    const syncHeader = () => {
      const s = db.get(DB_KEYS.settings)
      $('#adminName').textContent = s.adminName || 'Admin'
      document.title = `${s.pageName || 'Admin Dashboard'}`
    }

    // Global search
    $('#globalSearch').addEventListener('input', () => router.render())

    // Sidebar toggle (mobile)
    $('#toggleSidebar').onclick = () => $('#sidebar').classList.toggle('open')

    // INIT
    syncHeader()
    router.go(location.hash)
    })()
