function CodeBlock(el)
  if el.classes:includes("mermaid") then
    local hash = pandoc.utils.sha1(el.text)
    local input = "mermaid-" .. hash .. ".mmd"
    local output = "mermaid-" .. hash .. ".pdf"

    local f = io.open(input, "w")
    f:write(el.text)
    f:close()

    os.execute('mmdc -i "' .. input .. '" -o "' .. output .. '" -e pdf --pdfFit')

    return pandoc.Para{
      pandoc.Image({}, output)
    }
  end
end
