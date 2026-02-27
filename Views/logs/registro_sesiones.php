<?php encabezado() ?>

<div id="layoutSidenav_content">

    <main class="bg-gray-50/50 min-h-screen">

        <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-8">

            

            <div class="mb-8">

                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">

                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">

                        <i class="fas fa-business-time text-xl"></i>

                    </div>

                    Configuración de Turnos y Horarios

                </h2>

                <p class="text-sm text-gray-500 mt-1">Define los horarios laborales, tolerancias y días de trabajo para el cálculo automático de asistencias.</p>

            </div>


            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                

                <div class="lg:col-span-1">

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

                        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-plus-circle text-indigo-500 mr-2"></i>Nuevo Turno</h3>

                        

                        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Simulación: Turno guardado correctamente.');">

                            <div class="space-y-4">

                                <div>

                                    <label class="block text-xs font-bold text-gray-700 mb-1">Nombre del Turno</label>

                                    <input type="text" placeholder="Ej: Turno Administrativo" class="w-full text-sm px-3 py-2 border border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">

                                </div>

                                

                                <div class="grid grid-cols-2 gap-4">

                                    <div>

                                        <label class="block text-xs font-bold text-gray-700 mb-1">Hora Entrada</label>

                                        <input type="time" value="08:00" class="w-full text-sm px-3 py-2 border border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-gray-600">

                                    </div>

                                    <div>

                                        <label class="block text-xs font-bold text-gray-700 mb-1">Hora Salida</label>

                                        <input type="time" value="18:00" class="w-full text-sm px-3 py-2 border border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-gray-600">

                                    </div>

                                </div>


                                <div>

                                    <label class="block text-xs font-bold text-gray-700 mb-1">Tolerancia (Minutos)</label>

                                    <div class="relative">

                                        <input type="number" value="10" class="w-full text-sm px-3 py-2 border border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 pr-10">

                                        <span class="absolute right-3 top-2 text-xs text-gray-400 font-bold">MIN</span>

                                    </div>

                                    <p class="text-[10px] text-gray-400 mt-1">Tiempo de gracia antes de marcar llegada tardía.</p>

                                </div>


                                <div>

                                    <label class="block text-xs font-bold text-gray-700 mb-2">Días Laborales</label>

                                    <div class="flex flex-wrap gap-2">

                                        <label class="inline-flex items-center bg-gray-50 border border-gray-200 px-2 py-1 rounded-md cursor-pointer hover:bg-indigo-50 transition-colors">

                                            <input type="checkbox" checked class="text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 h-3 w-3 mr-1.5">

                                            <span class="text-xs text-gray-700 font-medium">Lun</span>

                                        </label>

                                        <label class="inline-flex items-center bg-gray-50 border border-gray-200 px-2 py-1 rounded-md cursor-pointer hover:bg-indigo-50 transition-colors">

                                            <input type="checkbox" checked class="text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 h-3 w-3 mr-1.5">

                                            <span class="text-xs text-gray-700 font-medium">Mar</span>

                                        </label>

                                        <label class="inline-flex items-center bg-gray-50 border border-gray-200 px-2 py-1 rounded-md cursor-pointer hover:bg-indigo-50 transition-colors">

                                            <input type="checkbox" checked class="text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 h-3 w-3 mr-1.5">

                                            <span class="text-xs text-gray-700 font-medium">Mié</span>

                                        </label>

                                        <label class="inline-flex items-center bg-gray-50 border border-gray-200 px-2 py-1 rounded-md cursor-pointer hover:bg-indigo-50 transition-colors">

                                            <input type="checkbox" checked class="text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 h-3 w-3 mr-1.5">

                                            <span class="text-xs text-gray-700 font-medium">Jue</span>

                                        </label>

                                        <label class="inline-flex items-center bg-gray-50 border border-gray-200 px-2 py-1 rounded-md cursor-pointer hover:bg-indigo-50 transition-colors">

                                            <input type="checkbox" checked class="text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 h-3 w-3 mr-1.5">

                                            <span class="text-xs text-gray-700 font-medium">Vie</span>

                                        </label>

                                        <label class="inline-flex items-center bg-gray-50 border border-gray-200 px-2 py-1 rounded-md cursor-pointer hover:bg-indigo-50 transition-colors">

                                            <input type="checkbox" class="text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 h-3 w-3 mr-1.5">

                                            <span class="text-xs text-gray-700 font-medium">Sáb</span>

                                        </label>

                                    </div>

                                </div>


                                <button type="submit" class="w-full mt-4 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2">

                                    <i class="fas fa-save"></i> Guardar Turno

                                </button>

                            </div>

                        </form>

                    </div>

                </div>


                <div class="lg:col-span-2">

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full">

                        <div class="overflow-x-auto">

                            <table class="w-full text-left border-collapse">

                                <thead>

                                    <tr class="bg-slate-800 border-b border-slate-900">

                                        <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider">Turno</th>

                                        <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider text-center">Horario</th>

                                        <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider text-center">Días</th>

                                        <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider text-center">Tolerancia</th>

                                        <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider text-center">Acciones</th>

                                    </tr>

                                </thead>

                                <tbody class="divide-y divide-gray-100">

                                    <tr class="hover:bg-indigo-50/30 transition-colors">

                                        <td class="px-6 py-4 text-sm font-bold text-gray-800">

                                            Administrativo Central

                                        </td>

                                        <td class="px-6 py-4 text-sm font-bold text-indigo-600 text-center whitespace-nowrap">

                                            08:00 - 18:00

                                        </td>

                                        <td class="px-6 py-4 text-xs text-gray-500 text-center">

                                            L, M, X, J, V

                                        </td>

                                        <td class="px-6 py-4 text-xs text-center">

                                            <span class="px-2 py-1 bg-green-50 text-green-700 border border-green-200 rounded-md font-medium">10 mins</span>

                                        </td>

                                        <td class="px-6 py-4 text-center">

                                            <button class="text-blue-500 hover:text-blue-700 mx-1" title="Editar"><i class="fas fa-edit"></i></button>

                                            <button class="text-red-500 hover:text-red-700 mx-1" title="Eliminar"><i class="fas fa-trash"></i></button>

                                        </td>

                                    </tr>


                                    <tr class="hover:bg-indigo-50/30 transition-colors">

                                        <td class="px-6 py-4 text-sm font-bold text-gray-800">

                                            Soporte Mañana (Sábados)

                                        </td>

                                        <td class="px-6 py-4 text-sm font-bold text-indigo-600 text-center whitespace-nowrap">

                                            08:00 - 12:00

                                        </td>

                                        <td class="px-6 py-4 text-xs text-gray-500 text-center">

                                            L, M, X, J, V, S

                                        </td>

                                        <td class="px-6 py-4 text-xs text-center">

                                            <span class="px-2 py-1 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-md font-medium">15 mins</span>

                                        </td>

                                        <td class="px-6 py-4 text-center">

                                            <button class="text-blue-500 hover:text-blue-700 mx-1" title="Editar"><i class="fas fa-edit"></i></button>

                                            <button class="text-red-500 hover:text-red-700 mx-1" title="Eliminar"><i class="fas fa-trash"></i></button>

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>


            </div>


        </div>

    </main>

    <?php pie() ?>

</div>