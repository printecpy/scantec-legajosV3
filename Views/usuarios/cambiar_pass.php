<?php encabezado($data); ?>

<main class="app-content bg-gray-50 min-h-screen py-2 font-sans">
    <div class="container mx-auto px-4">
        <div class="mb-6 flex items-center text-sm text-gray-500">
            <a href="<?php echo base_url(); ?>dashboard" class="hover:text-scantec-blue transition-colors">
                <i class="fas fa-home mr-1"></i> Inicio
            </a>
            <span class="mx-2">/</span>
            <span class="text-scantec-blue font-bold">Cambiar Contraseña</span>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                
                <div class="bg-scantec-blue px-8 py-6 border-b border-gray-100 text-center">
                    <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-3 text-white text-2xl">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h2 class="text-xl font-bold text-white tracking-wide">Actualizar Credenciales</h2>
                </div>

                <form id="formCambiarPass" method="post" action="<?php echo base_url(); ?>Usuarios/actualizar_password" autocomplete="off">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                    <div class="p-8 space-y-6">
                        <?php if (!empty($_SESSION['force_password_change'])): ?>
                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                                Este usuario fue creado con una contraseña temporal. Debe definir una nueva contraseña para poder continuar.
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Contraseña Actual</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-key"></i></div>
                                <input type="password" id="clave_actual" name="clave_actual" 
                                    class="pl-10 pr-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all" required>
                                <button type="button" onclick="togglePassword('clave_actual')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-scantec-blue focus:outline-none">
                                    <i class="fas fa-eye" id="icon_clave_actual"></i>
                                </button>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        <div>
                            <label class="block text-xs font-bold text-scantec-blue uppercase mb-2">Nueva Contraseña</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-shield-alt"></i></div>
                                <input type="password" id="clave_nueva" name="clave_nueva" 
                                    class="pl-10 pr-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all" required>
                                <button type="button" onclick="togglePassword('clave_nueva')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-scantec-blue focus:outline-none">
                                    <i class="fas fa-eye" id="icon_clave_nueva"></i>
                                </button>
                            </div>
                            <div class="w-full h-1 bg-gray-200 mt-2 rounded-full overflow-hidden">
                                <div id="strengthBar" class="h-full w-0 bg-red-500 transition-all duration-500"></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-scantec-blue uppercase mb-2">Repetir Contraseña</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-check-circle"></i></div>
                                <input type="password" id="clave_confirmar" name="clave_confirmar" 
                                    class="pl-10 pr-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all" required>
                            </div>
                            <p id="matchMessage" class="text-xs mt-1 font-bold h-4"></p>
                        </div>
                    </div>

                    <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex justify-end space-x-3">
                        <?php 
                            $cambioObligatorio = !empty($_SESSION['force_password_change']);
                            $rutaCancelar = ($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 2) 
                                ? base_url().'usuarios/listar' 
                                : base_url().'dashboard';
                        ?>
                        <?php if (!$cambioObligatorio): ?>
                            <a href="<?php echo $rutaCancelar; ?>" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-bold text-sm hover:bg-white transition-all">Cancelar</a>
                        <?php endif; ?>
                        
                        <button type="submit" class="px-8 py-2.5 rounded-xl bg-scantec-blue text-white font-bold text-sm shadow-md hover:bg-gray-800 transition-all">
                            Cambiar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    function togglePassword(id){
        var input = document.getElementById(id);
        var icon = document.getElementById('icon_'+id);
        if(input.type === "password"){ input.type = "text"; icon.classList.remove("fa-eye"); icon.classList.add("fa-eye-slash"); }
        else{ input.type = "password"; icon.classList.remove("fa-eye-slash"); icon.classList.add("fa-eye"); }
    }
    
    // Validaciones visuales (copia el bloque JS anterior aquí)
    const p1 = document.getElementById('clave_nueva');
    const p2 = document.getElementById('clave_confirmar');
    const msg = document.getElementById('matchMessage');
    const bar = document.getElementById('strengthBar');
    
    p1.addEventListener('input', function(){
        let v = this.value, s=0;
        if(v.length>5) s+=25; if(v.length>8) s+=25; if(/[A-Z]/.test(v)) s+=25; if(/[0-9]/.test(v)) s+=25;
        bar.style.width = s+'%';
        bar.className = (s<50) ? "h-full bg-red-500 transition-all" : (s<75 ? "h-full bg-yellow-400 transition-all" : "h-full bg-green-500 transition-all");
    });
    
    p2.addEventListener('input', function(){
        if(this.value === p1.value && this.value !== ''){ msg.textContent="Coinciden"; msg.className="text-xs mt-1 font-bold text-green-600"; }
        else{ msg.textContent="No coinciden"; msg.className="text-xs mt-1 font-bold text-red-500"; }
    });
</script>

<?php pie() ?>
