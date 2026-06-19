// AUTOLAND — Edge Function: crear-vendedor
// Crea un usuario de Auth + su perfil de "vendedor" sin exponer
// la service_role key en el frontend. Solo un admin puede llamarla.
//
// Deploy: supabase functions deploy crear-vendedor

import { createClient } from "https://esm.sh/@supabase/supabase-js@2";

const SUPABASE_URL = Deno.env.get("SUPABASE_URL")!;
const SERVICE_ROLE_KEY = Deno.env.get("SUPABASE_SERVICE_ROLE_KEY")!;

Deno.serve(async (req) => {
  const corsHeaders = {
    "Access-Control-Allow-Origin": "*",
    "Access-Control-Allow-Headers": "authorization, x-client-info, apikey, content-type",
  };

  if (req.method === "OPTIONS") {
    return new Response("ok", { headers: corsHeaders });
  }

  try {
    // Cliente con el token del usuario que llama, para verificar que es admin
    const authHeader = req.headers.get("Authorization") ?? "";
    const callerClient = createClient(SUPABASE_URL, SERVICE_ROLE_KEY, {
      global: { headers: { Authorization: authHeader } },
    });

    const { data: callerUser, error: callerErr } = await callerClient.auth.getUser();
    if (callerErr || !callerUser?.user) {
      return new Response(JSON.stringify({ error: "No autenticado." }), {
        status: 401,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    const adminClient = createClient(SUPABASE_URL, SERVICE_ROLE_KEY);

    const { data: perfilCaller } = await adminClient
      .from("perfiles")
      .select("rol")
      .eq("id", callerUser.user.id)
      .single();

    if (perfilCaller?.rol !== "admin") {
      return new Response(JSON.stringify({ error: "Solo un admin puede crear vendedores." }), {
        status: 403,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    const { usuario, nombreReal, password } = await req.json();

    if (!usuario || !nombreReal || !password || password.length < 6) {
      return new Response(JSON.stringify({ error: "Datos inválidos. La contraseña debe tener al menos 6 caracteres." }), {
        status: 400,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    const usuarioLimpio = String(usuario).trim().toLowerCase();
    const emailInterno = `${usuarioLimpio}@autoland.local`;

    // 1. Verificar que el usuario no exista ya
    const { data: existente } = await adminClient
      .from("perfiles")
      .select("id")
      .eq("usuario", usuarioLimpio)
      .maybeSingle();

    if (existente) {
      return new Response(JSON.stringify({ error: `El usuario "${usuarioLimpio}" ya existe.` }), {
        status: 409,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    // 2. Crear el usuario en Auth
    const { data: nuevoUser, error: createErr } = await adminClient.auth.admin.createUser({
      email: emailInterno,
      password,
      email_confirm: true,
    });

    if (createErr || !nuevoUser?.user) {
      return new Response(JSON.stringify({ error: createErr?.message ?? "No se pudo crear el usuario." }), {
        status: 400,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    // 3. Crear su perfil de vendedor
    const { error: perfilErr } = await adminClient.from("perfiles").insert({
      id: nuevoUser.user.id,
      usuario: usuarioLimpio,
      nombre_real: nombreReal,
      rol: "vendedor",
      activo: true,
    });

    if (perfilErr) {
      // Si falla el perfil, deshacemos el usuario de Auth para no dejar basura
      await adminClient.auth.admin.deleteUser(nuevoUser.user.id);
      return new Response(JSON.stringify({ error: perfilErr.message }), {
        status: 400,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    return new Response(JSON.stringify({ ok: true, usuario: usuarioLimpio }), {
      status: 200,
      headers: { ...corsHeaders, "Content-Type": "application/json" },
    });
  } catch (e) {
    return new Response(JSON.stringify({ error: String(e) }), {
      status: 500,
      headers: { ...corsHeaders, "Content-Type": "application/json" },
    });
  }
});
