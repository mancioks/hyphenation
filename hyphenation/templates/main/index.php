<form action="<?= BASE_URL ?>/main/hyphenate" method="post">
    <input type="text" name="word" placeholder="Word" required/>
    Pattern source: <select name="source">
        <option value="file">Text file</option>
        <option value="db">Database</option>
    </select>
    <input type="submit" value="Hyphenate"/>
</form>