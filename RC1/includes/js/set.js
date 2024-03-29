// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
myBbcodeSettings = {
  nameSpace:          "bbcode", // Useful to prevent multi-instances CSS conflict
  previewParserPath:  "quicktools/preview.php",
  markupSet: [
      {name:'Bold', key:'B', openWith:'[b]', closeWith:'[/b]'},
      {name:'Italic', key:'I', openWith:'[i]', closeWith:'[/i]'},
      {name:'Underline', key:'U', openWith:'[u]', closeWith:'[/u]'},
      {name:'Superscript', key:'1', openWith:'[sup]', closeWith:'[/sup]'},
      {name:'Subscript', key:'2', openWith:'[sub]', closeWith:'[/sub]'},
      {separator:'---------------' },
      {name:'Picture', key:'P', replaceWith:'[img][![Url]!][/img]'},
      {name:'Link', key:'L', openWith:'[url=[![Url]!]]', closeWith:'[/url]', placeHolder:'Your text to link here...'},
      {separator:'---------------' },
      {name:'Colors', openWith:'[color=[![Color]!]]', closeWith:'[/color]', dropMenu: [
 			{name:'Yellow',  replaceWith:'[color=#FCE94F]', closeWith:'[/color]', className:"col1-1" },
            {name:'Yellow',  openWith:'[color=#EDD400]', closeWith:'[/color]', className:"col1-2" },
            {name:'Yellow',  openWith:'[color=#C4A000]', closeWith:'[/color]', className:"col1-3" },
            {name:'Orange',  openWith:'[color=#FCAF3E]', closeWith:'[/color]', className:"col2-1" },
            {name:'Orange',  openWith:'[color=#F57900]', closeWith:'[/color]', className:"col2-2" },
            {name:'Orange',  openWith:'[color=#CE5C00]', closeWith:'[/color]', className:"col2-3" },
            {name:'Brown',   openWith:'[color=#E9B96E]', closeWith:'[/color]', className:"col3-1" },
            {name:'Brown',   openWith:'[color=#C17D11]', closeWith:'[/color]', className:"col3-2" },
            {name:'Brown',   openWith:'[color=#8F5902]', closeWith:'[/color]', className:"col3-3" },
            {name:'Green',   openWith:'[color=#8AE234]', closeWith:'[/color]', className:"col4-1" },
            {name:'Green',   openWith:'[color=#73D216]', closeWith:'[/color]', className:"col4-2" },
            {name:'Green',   openWith:'[color=#4E9A06]', closeWith:'[/color]', className:"col4-3" },
            {name:'Blue',    openWith:'[color=#729FCF]', closeWith:'[/color]', className:"col5-1" },
            {name:'Blue',    openWith:'[color=#3465A4]', closeWith:'[/color]', className:"col5-2" },
            {name:'Blue',    openWith:'[color=#204A87]', closeWith:'[/color]', className:"col5-3" },
            {name:'Purple',  openWith:'[color=#AD7FA8]', closeWith:'[/color]', className:"col6-1" },
            {name:'Purple',  openWith:'[color=#75507B]', closeWith:'[/color]', className:"col6-2" },
            {name:'Purple',  openWith:'[color=#5C3566]', closeWith:'[/color]', className:"col6-3" },
            {name:'Red',     openWith:'[color=#EF2929]', closeWith:'[/color]', className:"col7-1" },
            {name:'Red',     openWith:'[color=#CC0000]', closeWith:'[/color]', className:"col7-2" },
            {name:'Red',     openWith:'[color=#A40000]', closeWith:'[/color]', className:"col7-3" },
            {name:'White',    openWith:'[color=#FFFFFF]', closeWith:'[/color]', className:"col8-1" },
            {name:'Gray',    openWith:'[color=#D3D7CF]', closeWith:'[/color]', className:"col8-2" },
            {name:'Gray',    openWith:'[color=#BABDB6]', closeWith:'[/color]', className:"col8-3" },
            {name:'Gray',    openWith:'[color=#888A85]', closeWith:'[/color]', className:"col9-1" },
            {name:'Gray',    openWith:'[color=#555753]', closeWith:'[/color]', className:"col9-2" },
            {name:'Black',    openWith:'[color=#000000]', closeWith:'[/color]', className:"col9-3" }
      ]},
      {name:'Size', key:'S', openWith:'[size=[![Text size]!]]', closeWith:'[/size]', dropMenu :[
          {name:'Big', openWith:'[size=200]', closeWith:'[/size]' },
          {name:'Normal', openWith:'[size=100]', closeWith:'[/size]' },
          {name:'Small', openWith:'[size=50]', closeWith:'[/size]' }
      ]},
      {name:'Alignment', dropMenu :[
          {name:'Left', openWith:'[left]', closeWith:'[/left]' },
          {name:'Center', openWith:'[center]', closeWith:'[/center]' },
          {name:'Right', openWith:'[right]', closeWith:'[/right]' }
      ]},
      {separator:'---------------' },
      {name:'Bulleted list', openWith:'[list]\n', closeWith:'\n[/list]'},
      {name:'Numeric list', openWith:'[list=[![Starting number]!]]\n', closeWith:'\n[/list]'},
      {name:'List item', openWith:'[*] '},
      {separator:'---------------' },
      {name:'Quotes', openWith:'[quote]', closeWith:'[/quote]'},
      {name:'Code', openWith:'[code]', closeWith:'[/code]'},
      {separator:'---------------' },
      {name:'You Tube', openWith:'[youtube][![Video Code]!]', closeWith:'[/youtube]'},
      {name:'Attachments', className:"attach", openWith:function(markItUp) { validateUploadAPI(); } },
      {separator:'---------------' },
      {name:'Clean', className:"clean", replaceWith:function(h) { return h.selection.replace(/\[(.*?)\]/g, "") } },
      {name:'Spell Check', className:"spellChk", openWith:function(markItUp) { validateSpellAPI(markItUp); } },
      {name:'Preview', className:"preview", call:'preview' }
   ]
}

limitedBbcodeSettings = {
  nameSpace:          "limitedbbcode", // Useful to prevent multi-instances CSS conflict
  previewParserPath:  "quicktools/preview.php",
  markupSet: [
      {name:'Bold', key:'B', openWith:'[b]', closeWith:'[/b]'},
      {name:'Italic', key:'I', openWith:'[i]', closeWith:'[/i]'},
      {name:'Underline', key:'U', openWith:'[u]', closeWith:'[/u]'},
      {name:'Superscript', key:'1', openWith:'[sup]', closeWith:'[/sup]'},
      {name:'Subscript', key:'2', openWith:'[sub]', closeWith:'[/sub]'},
      {separator:'---------------' },
      {name:'Picture', key:'P', replaceWith:'[img][![Url]!][/img]'},
      {name:'Link', key:'L', openWith:'[url=[![Url]!]]', closeWith:'[/url]', placeHolder:'Your text to link here...'},
      {separator:'---------------' },
      {name:'Colors', openWith:'[color=[![Color]!]]', closeWith:'[/color]', dropMenu: [
 			{name:'Yellow',  replaceWith:'[color=#FCE94F]', closeWith:'[/color]', className:"col1-1" },
            {name:'Yellow',  openWith:'[color=#EDD400]', closeWith:'[/color]', className:"col1-2" },
            {name:'Yellow',  openWith:'[color=#C4A000]', closeWith:'[/color]', className:"col1-3" },
            {name:'Orange',  openWith:'[color=#FCAF3E]', closeWith:'[/color]', className:"col2-1" },
            {name:'Orange',  openWith:'[color=#F57900]', closeWith:'[/color]', className:"col2-2" },
            {name:'Orange',  openWith:'[color=#CE5C00]', closeWith:'[/color]', className:"col2-3" },
            {name:'Brown',   openWith:'[color=#E9B96E]', closeWith:'[/color]', className:"col3-1" },
            {name:'Brown',   openWith:'[color=#C17D11]', closeWith:'[/color]', className:"col3-2" },
            {name:'Brown',   openWith:'[color=#8F5902]', closeWith:'[/color]', className:"col3-3" },
            {name:'Green',   openWith:'[color=#8AE234]', closeWith:'[/color]', className:"col4-1" },
            {name:'Green',   openWith:'[color=#73D216]', closeWith:'[/color]', className:"col4-2" },
            {name:'Green',   openWith:'[color=#4E9A06]', closeWith:'[/color]', className:"col4-3" },
            {name:'Blue',    openWith:'[color=#729FCF]', closeWith:'[/color]', className:"col5-1" },
            {name:'Blue',    openWith:'[color=#3465A4]', closeWith:'[/color]', className:"col5-2" },
            {name:'Blue',    openWith:'[color=#204A87]', closeWith:'[/color]', className:"col5-3" },
            {name:'Purple',  openWith:'[color=#AD7FA8]', closeWith:'[/color]', className:"col6-1" },
            {name:'Purple',  openWith:'[color=#75507B]', closeWith:'[/color]', className:"col6-2" },
            {name:'Purple',  openWith:'[color=#5C3566]', closeWith:'[/color]', className:"col6-3" },
            {name:'Red',     openWith:'[color=#EF2929]', closeWith:'[/color]', className:"col7-1" },
            {name:'Red',     openWith:'[color=#CC0000]', closeWith:'[/color]', className:"col7-2" },
            {name:'Red',     openWith:'[color=#A40000]', closeWith:'[/color]', className:"col7-3" },
            {name:'White',    openWith:'[color=#FFFFFF]', closeWith:'[/color]', className:"col8-1" },
            {name:'Gray',    openWith:'[color=#D3D7CF]', closeWith:'[/color]', className:"col8-2" },
            {name:'Gray',    openWith:'[color=#BABDB6]', closeWith:'[/color]', className:"col8-3" },
            {name:'Gray',    openWith:'[color=#888A85]', closeWith:'[/color]', className:"col9-1" },
            {name:'Gray',    openWith:'[color=#555753]', closeWith:'[/color]', className:"col9-2" },
            {name:'Black',    openWith:'[color=#000000]', closeWith:'[/color]', className:"col9-3" }
      ]},
      {name:'Size', key:'S', openWith:'[size=[![Text size]!]]', closeWith:'[/size]', dropMenu :[
          {name:'Big', openWith:'[size=200]', closeWith:'[/size]' },
          {name:'Normal', openWith:'[size=100]', closeWith:'[/size]' },
          {name:'Small', openWith:'[size=50]', closeWith:'[/size]' }
      ]},
      {name:'Alignment', dropMenu :[
          {name:'Left', openWith:'[left]', closeWith:'[/left]' },
          {name:'Center', openWith:'[center]', closeWith:'[/center]' },
          {name:'Right', openWith:'[right]', closeWith:'[/right]' }
      ]},
      {separator:'---------------' },
      {name:'Bulleted list', openWith:'[list]\n', closeWith:'\n[/list]'},
      {name:'Numeric list', openWith:'[list=[![Starting number]!]]\n', closeWith:'\n[/list]'},
      {name:'List item', openWith:'[*] '},
      {separator:'---------------' },
      {name:'Quotes', openWith:'[quote]', closeWith:'[/quote]'},
      {name:'Code', openWith:'[code]', closeWith:'[/code]'},
	  {name:'You Tube', openWith:'[youtube][![Video Code]!]', closeWith:'[/youtube]'},
      {separator:'---------------' },
      {name:'Clean', className:"clean", replaceWith:function(h) { return h.selection.replace(/\[(.*?)\]/g, "") } },
      {name:'Spell Check', className:"spellChk", openWith:function(markItUp) { validateSpellAPI(markItUp); } },
      {name:'Preview', className:"preview", call:'preview' }
   ]
}